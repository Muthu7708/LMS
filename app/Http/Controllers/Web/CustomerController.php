<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerBankAccount;
use App\Models\CustomerEmployment;
use App\Models\CustomerKyc;
use App\Models\CustomerNominee;
use App\Models\CustomerReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Customer::with(['branch', 'activeLoans'])
            ->when(! $user->hasRole(['super_admin','company_admin']), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->branch_id, fn ($q) => $q->where('branch_id', $request->branch_id))
            ->latest();

        $customers = $query->paginate(20)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        return view('customers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:100',
            'middle_name'   => 'nullable|string|max:100',
            'last_name'     => 'required|string|max:100',
            'mobile'        => 'required|string|max:15|unique:customers,mobile',
            'email'         => 'nullable|email|unique:customers,email',
            'gender'        => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'pan'           => 'nullable|string|max:10|unique:customers,pan',
            'aadhaar'       => 'nullable|string|size:12',
            'marital_status'=> 'nullable|string',
            'branch_id'     => 'required|exists:branches,id',
            // Address
            'address_type'    => 'required|string',
            'address_line1'   => 'required|string',
            'address_line2'   => 'nullable|string',
            'city'            => 'required|string|max:100',
            'state'           => 'required|string|max:100',
            'pin_code'        => 'required|string|max:10',
        ]);

        $customer = DB::transaction(function () use ($data, $request) {
            $customer = Customer::create([
                ...$data,
                'company_id'  => Auth::user()->company_id,
                'created_by'  => Auth::id(),
                'customer_no' => $this->generateCustomerNo(),
                'status'      => 'active',
            ]);

            $addressType = match ($data['address_type']) {
                'residential' => 'current',
                'current', 'permanent', 'office', 'other' => $data['address_type'],
                default => 'current',
            };

            // Create primary address
            CustomerAddress::create([
                'customer_id'  => $customer->id,
                'type'         => $addressType,
                'address_line1'=> $data['address_line1'],
                'address_line2'=> $data['address_line2'] ?? null,
                'city'         => $data['city'],
                'state'        => $data['state'],
                'pin_code'     => $data['pin_code'],
                'is_primary'   => true,
            ]);

            return $customer;
        });

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer {$customer->customer_no} created successfully.");
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'addresses', 'contacts', 'kyc', 'employment', 'bankAccounts',
            'nominees', 'references', 'loans.branch', 'documents.currentVersion',
        ]);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $branches = Branch::active()->get();
        return view('customers.edit', compact('customer', 'branches'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:100',
            'middle_name'   => 'nullable|string|max:100',
            'last_name'     => 'required|string|max:100',
            'mobile'        => 'required|string|max:15|unique:customers,mobile,'.$customer->id,
            'email'         => 'nullable|email|unique:customers,email,'.$customer->id,
            'gender'        => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'pan'           => 'nullable|string|max:10|unique:customers,pan,'.$customer->id,
            'aadhaar'       => 'nullable|string|size:12',
            'marital_status'=> 'nullable|string',
        ]);

        $customer->update($data);
        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->activeLoans()->exists()) {
            return back()->with('error', 'Cannot delete a customer with active loans.');
        }
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    public function blacklist(Request $request, Customer $customer)
    {
        $request->validate(['reason' => 'required|string|min:10']);
        $customer->update([
            'status'          => 'blacklisted',
            'blacklist_reason'=> $request->reason,
            'blacklisted_at'  => now(),
            'blacklisted_by'  => Auth::id(),
        ]);
        return back()->with('success', "Customer {$customer->customer_no} has been blacklisted.");
    }

    public function unblacklist(Customer $customer)
    {
        $customer->update(['status' => 'active', 'blacklist_reason' => null, 'blacklisted_at' => null, 'blacklisted_by' => null]);
        return back()->with('success', "Customer {$customer->customer_no} has been reinstated.");
    }

    public function storeAddress(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'type'         => 'required|string|in:current,permanent,office,other,residential',
            'address_line1'=> 'required|string',
            'address_line2'=> 'nullable|string',
            'landmark'     => 'nullable|string',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'pin_code'     => 'required|string|max:10',
            'is_primary'   => 'boolean',
            'ownership'    => 'nullable|string',
        ]);

        if ($data['type'] === 'residential') {
            $data['type'] = 'current';
        }

        if ($request->boolean('is_primary')) {
            $customer->addresses()->update(['is_primary' => false]);
        }

        $customer->addresses()->create($data);
        return back()->with('success', 'Address added.');
    }

    public function storeKyc(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'document_type'     => 'required|string',
            'document_category' => 'required|string',
            'document_number'   => 'nullable|string',
            'issue_date'        => 'nullable|date',
            'expiry_date'       => 'nullable|date|after:today',
        ]);
        $customer->kyc()->create($data);
        return back()->with('success', 'KYC document added.');
    }

    public function storeEmployment(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'employment_type' => 'required|string',
            'employer_name'   => 'nullable|string|max:200',
            'designation'     => 'nullable|string|max:100',
            'monthly_income'  => 'required|numeric|min:0',
            'other_income'    => 'nullable|numeric|min:0',
        ]);
        $customer->employment()->update(['is_current' => false]);
        $customer->employment()->create([...$data, 'is_current' => true]);
        return back()->with('success', 'Employment details saved.');
    }

    public function storeBankAccount(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'bank_name'           => 'required|string|max:150',
            'account_number'      => 'required|string|max:30',
            'account_holder_name' => 'required|string|max:200',
            'account_type'        => 'required|string',
            'ifsc_code'           => 'nullable|string|max:15',
            'is_primary'          => 'boolean',
        ]);
        if ($request->boolean('is_primary')) {
            $customer->bankAccounts()->update(['is_primary' => false]);
        }
        $customer->bankAccounts()->create($data);
        return back()->with('success', 'Bank account added.');
    }

    public function storeNominee(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'relationship'  => 'required|string|max:100',
            'date_of_birth' => 'nullable|date',
            'phone'         => 'nullable|string|max:15',
            'share_percent' => 'required|numeric|min:0|max:100',
        ]);
        $customer->nominees()->create($data);
        return back()->with('success', 'Nominee added.');
    }

    public function storeReference(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'phone'        => 'required|string|max:15',
            'relationship' => 'nullable|string|max:100',
            'occupation'   => 'nullable|string|max:100',
        ]);
        $customer->references()->create($data);
        return back()->with('success', 'Reference added.');
    }

    protected function generateCustomerNo(): string
    {
        $last = Customer::withTrashed()->orderByDesc('id')->lockForUpdate()->first();
        $num = 1;
        if ($last && preg_match('/(\d+)$/', $last->customer_no, $matches)) {
            $num = ((int) $matches[1]) + 1;
        }
        return 'CUST-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
