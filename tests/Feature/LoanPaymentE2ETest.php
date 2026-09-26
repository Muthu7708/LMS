<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\User;
use App\Services\EmiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoanPaymentE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $user;
    protected Customer $customer;
    protected Loan $loan;
    protected EmiService $emiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Payment Co',
            'code' => 'PAYCO',
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
            'name' => 'Cashier',
            'email' => 'cashier_' . time() . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $permissions = [
            'loan.view', 'payment.collect', 'payment.receipt.reprint', 'payment.reverse'
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
            'first_name' => 'Alice',
            'last_name' => 'Payer',
            'mobile' => '90' . rand(10000000, 99999999),
            'status' => 'active',
        ]);

        // Create & Disburse Loan
        $this->loan = Loan::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'loan_officer_id' => $this->user->id,
            'loan_no' => 'LN-PAY-' . rand(10000, 99999),
            'application_date' => now(),
            'loan_type' => 'personal',
            'applied_amount' => 120000,
            'approved_amount' => 120000,
            'disbursed_amount' => 120000,
            'outstanding_principal' => 120000,
            'interest_rate' => 12.0,
            'tenure_months' => 12,
            'emi_amount' => 10661.85,
            'interest_method' => 'reducing',
            'payment_frequency' => 'monthly',
            'status' => 'active',
            'disbursed_at' => now(),
            'first_emi_date' => now()->addDays(30),
        ]);

        $this->emiService = app(EmiService::class);
        $this->emiService->generateSchedule($this->loan);
    }

    public function test_can_simulate_emi(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('loans.simulate'), [
            'principal' => 100000,
            'annual_rate' => 12,
            'tenure_months' => 12,
            'method' => 'reducing',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['emi_amount', 'total_interest', 'total_payable', 'schedule']);
    }

    public function test_can_render_payment_collection_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('loans.payments.create', $this->loan));
        $response->assertStatus(200);
    }

    public function test_can_collect_payment_and_generate_receipt(): void
    {
        $this->withoutExceptionHandling();
        $firstEmi = $this->loan->emiSchedules->first();
        $payAmount = (float) $firstEmi->emi_amount;

        $response = $this->actingAs($this->user)->post(route('loans.payments.store', $this->loan), [
            'amount' => $payAmount,
            'payment_date' => date('Y-m-d'),
            'payment_mode' => 'cash',
            'remarks' => 'First EMI payment',
        ]);

        $payment = LoanPayment::where('loan_id', $this->loan->id)->first();
        $this->assertNotNull($payment);
        $response->assertRedirect(route('loans.payments.receipt', $payment));
        $this->assertEquals($payAmount, $payment->amount);
        $this->assertEquals('paid', $firstEmi->fresh()->status);
    }

    public function test_can_reprint_payment_receipt(): void
    {
        $firstEmi = $this->loan->emiSchedules->first();
        $payment = $this->emiService->processPayment($this->loan, (float) $firstEmi->emi_amount, [
            'payment_date' => date('Y-m-d'),
            'payment_mode' => 'cash',
            'collected_by' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('loans.payments.reprint', $payment), [
            'reason' => 'Customer requested physical duplicate copy',
        ]);

        $response->assertRedirect(route('loans.payments.receipt', $payment));
        $this->assertCount(1, $payment->fresh()->reprints);
    }

    public function test_can_reverse_payment(): void
    {
        $firstEmi = $this->loan->emiSchedules->first();
        $payment = $this->emiService->processPayment($this->loan, (float) $firstEmi->emi_amount, [
            'payment_date' => date('Y-m-d'),
            'payment_mode' => 'cash',
            'collected_by' => $this->user->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('loans.payments.reverse', $payment), [
            'reversal_reason' => 'Cheque bounced / erroneous entry',
        ]);

        $response->assertRedirect();
        $this->assertTrue($payment->fresh()->is_reversed);
    }

    public function test_cannot_collect_payment_on_non_active_loan(): void
    {
        $this->loan->update(['status' => 'draft', 'outstanding_principal' => 0]);

        $response = $this->actingAs($this->user)->get(route('loans.payments.create', $this->loan));
        $response->assertRedirect(route('loans.show', $this->loan));
        $response->assertSessionHas('error');

        $responseStore = $this->actingAs($this->user)->post(route('loans.payments.store', $this->loan), [
            'amount' => 1000,
            'payment_date' => date('Y-m-d'),
            'payment_mode' => 'cash',
        ]);
        $responseStore->assertRedirect(route('loans.show', $this->loan));
        $responseStore->assertSessionHas('error');
    }
}
