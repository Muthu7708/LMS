<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoanLifecycleE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Loan Lifecycle Co',
            'code' => 'LLC',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'Main Branch',
            'code' => 'MB01',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Loan Officer',
            'email' => 'loanofficer_' . time() . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $permissions = [
            'loan.view', 'loan.create', 'loan.edit', 'loan.submit',
            'loan.verify', 'loan.approve', 'loan.reject', 'loan.disburse', 'loan.close'
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-' . rand(10000, 99999),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'mobile' => '91' . rand(10000000, 99999999),
            'status' => 'active',
        ]);
    }

    public function test_can_list_loans(): void
    {
        $response = $this->actingAs($this->user)->get(route('loans.index'));
        $response->assertStatus(200);
    }

    public function test_can_render_create_loan_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('loans.create'));
        $response->assertStatus(200);
    }

    public function test_can_create_draft_loan(): void
    {
        $response = $this->actingAs($this->user)->post(route('loans.store'), [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'loan_type' => 'personal',
            'applied_amount' => 100000,
            'interest_rate' => 12.0,
            'tenure_months' => 12,
            'repayment_frequency' => 'monthly',
            'interest_type' => 'reducing',
            'first_emi_date' => date('Y-m-d', strtotime('+30 days')),
            'purpose' => 'Business expansion',
        ]);

        $loan = Loan::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($loan);
        $response->assertRedirect(route('loans.show', $loan));
        $this->assertEquals('draft', $loan->status);
        $this->assertEquals(100000, $loan->applied_amount);
    }

    public function test_can_add_collateral_and_guarantor_to_loan(): void
    {
        $this->actingAs($this->user)->post(route('loans.store'), [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'loan_type' => 'vehicle',
            'applied_amount' => 200000,
            'interest_rate' => 10.0,
            'tenure_months' => 24,
            'repayment_frequency' => 'monthly',
            'interest_type' => 'reducing',
            'first_emi_date' => date('Y-m-d', strtotime('+30 days')),
        ]);

        $loan = Loan::where('customer_id', $this->customer->id)->first();

        // Add Collateral
        $this->actingAs($this->user)->post(route('loans.collaterals.store', $loan), [
            'collateral_type' => 'vehicle',
            'title' => 'Honda City 2022',
            'estimated_value' => 250000,
            'description' => 'Registration No MH01AB1234',
        ]);
        $this->assertCount(1, $loan->fresh()->collaterals);

        // Add Guarantor
        $this->actingAs($this->user)->post(route('loans.guarantors.store', $loan), [
            'name' => 'Guarantor Person',
            'relationship' => 'Brother',
            'mobile' => '9200000000',
        ]);
        $this->assertCount(1, $loan->fresh()->guarantors);
    }

    public function test_full_loan_approval_and_disbursement_flow(): void
    {
        $this->actingAs($this->user)->post(route('loans.store'), [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'loan_type' => 'personal',
            'applied_amount' => 50000,
            'interest_rate' => 12.0,
            'tenure_months' => 6,
            'repayment_frequency' => 'monthly',
            'interest_type' => 'reducing',
            'first_emi_date' => date('Y-m-d', strtotime('+30 days')),
        ]);

        $loan = Loan::where('customer_id', $this->customer->id)->first();

        // 1. Submit
        $this->actingAs($this->user)->post(route('loans.submit', $loan));
        $this->assertEquals('submitted', $loan->fresh()->status);

        // 2. Verify
        $this->actingAs($this->user)->post(route('loans.verify', $loan), [
            'verification_type' => 'field',
            'status' => 'passed',
            'remarks' => 'Verification clean',
        ]);
        $this->assertEquals('verified', $loan->fresh()->status);

        // 3. Approve
        $this->actingAs($this->user)->post(route('loans.approve', $loan), [
            'approved_amount' => 50000,
            'approved_rate' => 12.0,
            'approved_tenure' => 6,
            'remarks' => 'Approved in full',
        ]);
        $this->assertEquals('approved', $loan->fresh()->status);

        // 4. Disburse
        $this->actingAs($this->user)->post(route('loans.disburse', $loan), [
            'amount' => 50000,
            'mode' => 'bank_transfer',
            'reference_no' => 'UTR' . time(),
            'bank_name' => 'ICICI Bank',
            'account_number' => '9876543210',
            'disbursement_date' => date('Y-m-d'),
        ]);

        $loan->refresh();
        $this->assertEquals('active', $loan->status);
        $this->assertEquals(50000, $loan->disbursed_amount);
        $this->assertCount(6, $loan->emiSchedules);
    }

    public function test_loan_rejection_flow(): void
    {
        $this->actingAs($this->user)->post(route('loans.store'), [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'loan_type' => 'personal',
            'applied_amount' => 500000,
            'interest_rate' => 15.0,
            'tenure_months' => 36,
            'repayment_frequency' => 'monthly',
            'interest_type' => 'reducing',
            'first_emi_date' => date('Y-m-d', strtotime('+30 days')),
        ]);

        $loan = Loan::where('customer_id', $this->customer->id)->first();
        $this->actingAs($this->user)->post(route('loans.submit', $loan));

        // Reject Loan
        $this->actingAs($this->user)->post(route('loans.reject', $loan), [
            'reason' => 'Credit score below threshold',
        ]);

        $this->assertEquals('rejected', $loan->fresh()->status);
    }
}
