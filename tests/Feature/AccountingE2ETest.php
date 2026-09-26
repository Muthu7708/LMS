<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountingE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected Company $company;
    protected Branch $branch;
    protected User $user;
    protected ChartOfAccount $cashAccount;
    protected ChartOfAccount $equityAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Accounting Co',
            'code' => 'ACCCO',
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
            'name' => 'Accountant',
            'email' => 'accountant_' . time() . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->user->assignRole($role);

        $permissions = [
            'accounting.view', 'accounting.coa.manage',
            'accounting.journal.create', 'accounting.journal.post'
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $role->givePermissionTo($perm);
        }

        $codePrefix = rand(100, 999);
        $this->cashAccount = ChartOfAccount::create([
            'company_id' => $this->company->id,
            'code' => '1010-' . $codePrefix,
            'name' => 'Cash in Hand',
            'account_type' => 'asset',
            'account_sub_type' => 'cash',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);

        $this->equityAccount = ChartOfAccount::create([
            'company_id' => $this->company->id,
            'code' => '3010-' . $codePrefix,
            'name' => 'Owner Equity',
            'account_type' => 'equity',
            'account_sub_type' => 'capital',
            'normal_balance' => 'credit',
            'is_active' => true,
        ]);
    }

    public function test_can_view_accounting_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('accounting.index'));
        $response->assertStatus(200);
    }

    public function test_can_view_coa(): void
    {
        $response = $this->actingAs($this->user)->get(route('accounting.coa'));
        $response->assertStatus(200);
    }

    public function test_can_create_chart_of_account(): void
    {
        $code = '5010-' . rand(1000, 9999);
        $response = $this->actingAs($this->user)->post(route('accounting.coa.store'), [
            'code' => $code,
            'name' => 'Office Expense',
            'account_type' => 'expense',
            'account_sub_type' => 'operating_expense',
            'normal_balance' => 'debit',
        ]);

        $response->assertRedirect();
        $account = ChartOfAccount::where('code', $code)->first();
        $this->assertNotNull($account);
        $this->assertEquals('Office Expense', $account->name);
    }

    public function test_can_create_and_post_journal_entry(): void
    {
        $response = $this->actingAs($this->user)->post(route('accounting.journal.store'), [
            'entry_date' => date('Y-m-d'),
            'narration' => 'Initial capital injection',
            'lines' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'type' => 'debit',
                    'amount' => 500000,
                    'description' => 'Cash received',
                ],
                [
                    'account_id' => $this->equityAccount->id,
                    'type' => 'credit',
                    'amount' => 500000,
                    'description' => 'Owner contribution',
                ],
            ],
        ]);

        $response->assertRedirect(route('accounting.journal'));
        $journal = JournalEntry::where('company_id', $this->company->id)->first();
        $this->assertNotNull($journal);

        // Post Journal
        $postResponse = $this->actingAs($this->user)->post(route('accounting.journal.post', $journal));
        $postResponse->assertRedirect();
        $this->assertEquals('posted', $journal->fresh()->status);
    }

    public function test_can_view_financial_reports(): void
    {
        $this->actingAs($this->user)->get(route('accounting.ledger'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('accounting.trial-balance'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('accounting.profit-loss'))->assertStatus(200);
        $this->actingAs($this->user)->get(route('accounting.balance-sheet'))->assertStatus(200);
    }
}
