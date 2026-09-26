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

class LoanEntryTest extends TestCase
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
            'name' => 'Test Finance Ltd',
            'code' => 'TFL',
            'email' => 'info@testfinance.com',
            'phone' => '9999999999',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'Main Branch',
            'code' => 'MB01',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'name' => 'Loan Officer Test',
            'email' => 'loanofficer_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Setup permissions
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $this->user->assignRole($role);

        $permissions = [
            'loan.view', 'loan.create', 'loan.submit',
            'loan.verify', 'loan.approve', 'loan.disburse'
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
            $role->givePermissionTo($perm);
        }

        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'customer_no' => 'CUST-' . time(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'mobile' => '9876543210',
            'status' => 'active',
        ]);
    }

    public function test_can_simulate_emi(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('loans.simulate'), [
            'principal' => 100000,
            'annual_rate' => 12.0,
            'tenure_months' => 12,
            'method' => 'reducing',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'emi_amount',
            'total_interest',
            'total_payable',
            'schedule',
        ]);
    }

    public function test_can_create_draft_loan_application(): void
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
            'purpose' => 'Home Improvement',
            'remarks' => 'Test application',
        ]);

        $loan = Loan::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($loan);
        $response->assertRedirect(route('loans.show', $loan));
        $this->assertEquals('draft', $loan->status);
        $this->assertEquals(100000, $loan->applied_amount);
        $this->assertEquals('reducing', $loan->interest_method);
        $this->assertGreaterThan(0, $loan->emi_amount);
    }

    public function test_full_loan_lifecycle_disbursement_and_schedule(): void
    {
        $this->withoutExceptionHandling();
        // 1. Create Draft
        $this->actingAs($this->user)->post(route('loans.store'), [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'loan_type' => 'personal',
            'applied_amount' => 50000,
            'interest_rate' => 10.0,
            'tenure_months' => 6,
            'repayment_frequency' => 'monthly',
            'interest_type' => 'reducing',
            'first_emi_date' => date('Y-m-d', strtotime('+30 days')),
        ]);

        $loan = Loan::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($loan);

        // 2. Submit Application
        $this->actingAs($this->user)->post(route('loans.submit', $loan));
        $this->assertEquals('submitted', $loan->fresh()->status);

        // 3. Verify Application
        $this->actingAs($this->user)->post(route('loans.verify', $loan), [
            'verification_type' => 'field',
            'status' => 'passed',
            'remarks' => 'Verified customer residence',
        ]);
        $this->assertEquals('verified', $loan->fresh()->status);

        // 4. Approve Application
        $this->actingAs($this->user)->post(route('loans.approve', $loan), [
            'approved_amount' => 50000,
            'approved_rate' => 10.0,
            'approved_tenure' => 6,
            'remarks' => 'Approved',
        ]);
        $this->assertEquals('approved', $loan->fresh()->status);

        // 5. Disburse Loan
        $this->actingAs($this->user)->post(route('loans.disburse', $loan), [
            'amount' => 50000,
            'mode' => 'bank_transfer',
            'reference_no' => 'UTR12345678',
            'bank_name' => 'HDFC Bank',
            'account_number' => '987654321',
            'disbursement_date' => date('Y-m-d'),
        ]);

        $loan->refresh();
        $this->assertEquals('active', $loan->status);
        $this->assertEquals(50000, $loan->disbursed_amount);
        $this->assertCount(6, $loan->emiSchedules);

        $firstEmi = $loan->emiSchedules->first();
        $this->assertGreaterThan(0, $firstEmi->emi_amount);
        $this->assertGreaterThan(0, $firstEmi->principal_amount);
        $this->assertGreaterThan(0, $firstEmi->interest_amount);
    }
}
