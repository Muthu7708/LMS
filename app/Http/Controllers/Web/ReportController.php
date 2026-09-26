<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanEmiSchedule;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function disbursement(Request $request)
    {
        $query = Loan::with(['customer', 'branch'])
            ->whereNotNull('disbursement_date')
            ->when($request->start_date, fn ($q, $d) => $q->whereDate('disbursement_date', '>=', $d))
            ->when($request->end_date, fn ($q, $d) => $q->whereDate('disbursement_date', '<=', $d))
            ->when($request->branch_id, fn ($q, $b) => $q->where('branch_id', $b))
            ->latest('disbursement_date');

        $disbursements = $query->paginate(20)->withQueryString();
        $totalAmount   = (clone $query)->sum('disbursed_amount');

        return view('reports.disbursement', compact('disbursements', 'totalAmount'));
    }

    public function collection(Request $request)
    {
        $query = LoanPayment::with(['loan.customer', 'branch', 'collectedBy'])
            ->when($request->start_date, fn ($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($request->end_date, fn ($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->when($request->payment_mode, fn ($q, $m) => $q->where('payment_mode', $m))
            ->latest('payment_date');

        $payments    = $query->paginate(20)->withQueryString();
        $totalAmount = (clone $query)->sum('amount');

        return view('reports.collection', compact('payments', 'totalAmount'));
    }

    public function overdue(Request $request)
    {
        $query = LoanEmiSchedule::with(['loan.customer', 'loan.branch'])
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('due_date', '<', now())
            ->orderBy('due_date');

        $overdueEmis  = $query->paginate(20)->withQueryString();
        $totalOverdue = (clone $query)->sum('emi_amount');

        return view('reports.overdue', compact('overdueEmis', 'totalOverdue'));
    }

    public function portfolio(Request $request)
    {
        $loans = Loan::with(['customer', 'branch'])->whereIn('status', ['active', 'overdue', 'npa'])->get();
        $totalPrincipal = $loans->sum('outstanding_principal');
        return view('reports.portfolio', compact('loans', 'totalPrincipal'));
    }

    public function customerStatement(Request $request)
    {
        $customers = Customer::where('status', 'active')->get();
        $selectedCustomer = null;
        $loans = collect();

        if ($request->customer_id) {
            $selectedCustomer = Customer::with(['loans.payments', 'loans.emiSchedules'])->find($request->customer_id);
        }

        return view('reports.customer-statement', compact('customers', 'selectedCustomer'));
    }

    public function npa()
    {
        $npaLoans = Loan::with(['customer', 'branch'])->where('status', 'npa')->get();
        return view('reports.npa', compact('npaLoans'));
    }

    public function disbursementPdf(Request $request)
    {
        return response('Disbursement Report PDF Stream', 200, ['Content-Type' => 'application/pdf']);
    }

    public function disbursementExcel(Request $request)
    {
        return response('Disbursement Report Excel Stream', 200, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function collectionPdf(Request $request)
    {
        return response('Collection Report PDF Stream', 200, ['Content-Type' => 'application/pdf']);
    }

    public function collectionExcel(Request $request)
    {
        return response('Collection Report Excel Stream', 200, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function overduePdf(Request $request)
    {
        return response('Overdue Report PDF Stream', 200, ['Content-Type' => 'application/pdf']);
    }

    public function overdueExcel(Request $request)
    {
        return response('Overdue Report Excel Stream', 200, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function customerStatementPdf(Customer $customer)
    {
        return response('Customer Statement PDF Stream', 200, ['Content-Type' => 'application/pdf']);
    }
}
