<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanWaiver;
use App\Models\User;
use App\Services\EmiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoanSettlementAndForeclosureE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $user;
    protected Customer $customer;
    protected Loan $loan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Settlement Co',
            'code' => 'SETCO',
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
            'name' => 'Loan Manager',
            'email' => 'loanmgr_' . time() . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $permissions = [
            'loan.view', 'waiver.request', 'waiver.approve',
            'loan.restructure', 'loan.foreclosure'
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
            'first_name' => 'Sam',
            'last_name' => 'Settler',
            'mobile' => '89' . rand(10000000, 99999999),
            'status' => 'active',
        ]);

        $this->loan = Loan::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'loan_officer_id' => $this->user->id,
            'loan_no' => 'LN-SET-' . rand(10000, 99999),
            'application_date' => now(),
            'loan_type' => 'personal',
            'applied_amount' => 100000,
            'approved_amount' => 100000,
            'disbursed_amount' => 100000,
            'outstanding_principal' => 100000,
            'outstanding_interest' => 2000,
            'outstanding_penalty' => 500,
            'interest_rate' => 12.0,
            'tenure_months' => 12,
            'emi_amount' => 8884.88,
            'interest_method' => 'reducing',
            'payment_frequency' => 'monthly',
            'status' => 'active',
            'disbursed_at' => now(),
            'first_emi_date' => now()->addDays(30),
        ]);

        app(EmiService::class)->generateSchedule($this->loan);
    }

    public function test_can_request_and_approve_waiver(): void
    {
        // Request Waiver
        $response = $this->actingAs($this->user)->post(route('loans.waivers.request', $this->loan), [
            'waiver_type' => 'penalty',
            'requested_amount' => 500,
            'reason' => 'Customer experienced financial hardship due to medical emergency',
        ]);
        $response->assertRedirect();

        $waiver = LoanWaiver::where('loan_id', $this->loan->id)->first();
        $this->assertNotNull($waiver);
        $this->assertEquals('pending', $waiver->status);

        // Approve Waiver
        $response = $this->actingAs($this->user)->post(route('loans.waivers.approve', $waiver), [
            'approved_amount' => 500,
            'remarks' => 'Waived full penalty amount',
        ]);
        $response->assertRedirect();
        $this->assertEquals('approved', $waiver->fresh()->status);
    }

    public function test_can_restructure_loan(): void
    {
        $response = $this->actingAs($this->user)->post(route('loans.restructure', $this->loan), [
            'new_interest_rate' => 10.0,
            'new_tenure_months' => 18,
            'reason' => 'Lower monthly EMI requested by borrower',
        ]);

        $response->assertRedirect();
        $this->assertEquals('restructured', $this->loan->fresh()->status);
        $this->assertEquals(10.0, (float)$this->loan->fresh()->interest_rate);
        $this->assertEquals(18, $this->loan->fresh()->tenure_months);
    }

    public function test_can_render_foreclosure_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('loans.foreclosure.form', $this->loan));
        $response->assertStatus(200);
    }

    public function test_can_foreclose_and_close_loan(): void
    {
        $response = $this->actingAs($this->user)->post(route('loans.foreclosure', $this->loan), [
            'payment_mode' => 'neft',
            'payment_reference' => 'UTR9988776655',
        ]);

        $response->assertRedirect(route('loans.show', $this->loan));
        $this->assertEquals('closed', $this->loan->fresh()->status);
        $this->assertEquals(0, (float)$this->loan->fresh()->outstanding_principal);
    }

    public function test_cannot_restructure_or_foreclose_non_active_loan(): void
    {
        $this->loan->update(['status' => 'draft', 'outstanding_principal' => 0]);

        // Try to restructure draft loan
        $responseRestructure = $this->actingAs($this->user)->post(route('loans.restructure', $this->loan), [
            'new_interest_rate' => 10.0,
            'new_tenure_months' => 18,
            'reason' => 'Lower monthly EMI',
        ]);
        $responseRestructure->assertRedirect(route('loans.show', $this->loan));
        $responseRestructure->assertSessionHas('error');

        // Try to access foreclosure form for draft loan
        $responseForeclosureForm = $this->actingAs($this->user)->get(route('loans.foreclosure.form', $this->loan));
        $responseForeclosureForm->assertRedirect(route('loans.show', $this->loan));
        $responseForeclosureForm->assertSessionHas('error');

        // Try to foreclose draft loan
        $responseForeclose = $this->actingAs($this->user)->post(route('loans.foreclosure', $this->loan), [
            'payment_mode' => 'neft',
            'payment_reference' => 'UTR9988776655',
        ]);
        $responseForeclose->assertRedirect(route('loans.show', $this->loan));
        $responseForeclose->assertSessionHas('error');
    }
}
