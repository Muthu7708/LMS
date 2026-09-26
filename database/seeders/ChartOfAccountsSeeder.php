<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('code', 'MAIN')->first();
        if (! $company) {
            $this->command->warn('No company found. Run CompanySeeder first.');
            return;
        }

        $cid = $company->id;

        // Helper closure
        $create = function (array $data) use ($cid) {
            return ChartOfAccount::firstOrCreate(
                ['company_id' => $cid, 'code' => $data['code']],
                array_merge($data, ['company_id' => $cid, 'is_system_account' => true])
            );
        };

        // -------------------------------------------------------
        // ASSETS
        // -------------------------------------------------------
        $assets = $create(['code' => '1000', 'name' => 'Assets', 'account_type' => 'asset', 'account_sub_type' => 'current_asset', 'normal_balance' => 'debit', 'sort_order' => 1, 'allow_manual_entry' => false]);

        $create(['code' => '1100', 'name' => 'Cash in Hand', 'account_type' => 'asset', 'account_sub_type' => 'cash', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 2]);
        $create(['code' => '1200', 'name' => 'Bank Accounts', 'account_type' => 'asset', 'account_sub_type' => 'bank', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 3, 'allow_manual_entry' => false]);
        $create(['code' => '1201', 'name' => 'Main Bank Account', 'account_type' => 'asset', 'account_sub_type' => 'bank', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 4, 'is_bank_account' => true]);
        $create(['code' => '1300', 'name' => 'Loan Portfolio (Principal)', 'account_type' => 'asset', 'account_sub_type' => 'loan_portfolio', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 5, 'allow_manual_entry' => false]);
        $create(['code' => '1301', 'name' => 'Accrued Interest Receivable', 'account_type' => 'asset', 'account_sub_type' => 'receivable', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 6, 'allow_manual_entry' => false]);
        $create(['code' => '1302', 'name' => 'Penalty Receivable', 'account_type' => 'asset', 'account_sub_type' => 'receivable', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 7, 'allow_manual_entry' => false]);
        $create(['code' => '1400', 'name' => 'Processing Fee Receivable', 'account_type' => 'asset', 'account_sub_type' => 'receivable', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 8]);
        $create(['code' => '1500', 'name' => 'Fixed Assets', 'account_type' => 'asset', 'account_sub_type' => 'fixed_asset', 'normal_balance' => 'debit', 'parent_id' => $assets->id, 'sort_order' => 9]);
        $create(['code' => '1600', 'name' => 'NPA Provisions', 'account_type' => 'asset', 'account_sub_type' => 'receivable', 'normal_balance' => 'credit', 'parent_id' => $assets->id, 'sort_order' => 10]);

        // -------------------------------------------------------
        // LIABILITIES
        // -------------------------------------------------------
        $liabilities = $create(['code' => '2000', 'name' => 'Liabilities', 'account_type' => 'liability', 'account_sub_type' => 'current_liability', 'normal_balance' => 'credit', 'sort_order' => 20, 'allow_manual_entry' => false]);

        $create(['code' => '2100', 'name' => 'Customer Deposits', 'account_type' => 'liability', 'account_sub_type' => 'current_liability', 'normal_balance' => 'credit', 'parent_id' => $liabilities->id, 'sort_order' => 21]);
        $create(['code' => '2200', 'name' => 'Borrowings / Borrowed Funds', 'account_type' => 'liability', 'account_sub_type' => 'long_term_liability', 'normal_balance' => 'credit', 'parent_id' => $liabilities->id, 'sort_order' => 22]);
        $create(['code' => '2300', 'name' => 'GST Payable', 'account_type' => 'liability', 'account_sub_type' => 'current_liability', 'normal_balance' => 'credit', 'parent_id' => $liabilities->id, 'sort_order' => 23]);
        $create(['code' => '2400', 'name' => 'TDS Payable', 'account_type' => 'liability', 'account_sub_type' => 'current_liability', 'normal_balance' => 'credit', 'parent_id' => $liabilities->id, 'sort_order' => 24]);
        $create(['code' => '2500', 'name' => 'Advance EMI Received', 'account_type' => 'liability', 'account_sub_type' => 'current_liability', 'normal_balance' => 'credit', 'parent_id' => $liabilities->id, 'sort_order' => 25]);

        // -------------------------------------------------------
        // EQUITY
        // -------------------------------------------------------
        $equity = $create(['code' => '3000', 'name' => 'Equity', 'account_type' => 'equity', 'account_sub_type' => 'capital', 'normal_balance' => 'credit', 'sort_order' => 30, 'allow_manual_entry' => false]);

        $create(['code' => '3100', 'name' => 'Share Capital', 'account_type' => 'equity', 'account_sub_type' => 'capital', 'normal_balance' => 'credit', 'parent_id' => $equity->id, 'sort_order' => 31]);
        $create(['code' => '3200', 'name' => 'Retained Earnings', 'account_type' => 'equity', 'account_sub_type' => 'retained_earnings', 'normal_balance' => 'credit', 'parent_id' => $equity->id, 'sort_order' => 32]);
        $create(['code' => '3300', 'name' => 'Current Year Profit / Loss', 'account_type' => 'equity', 'account_sub_type' => 'retained_earnings', 'normal_balance' => 'credit', 'parent_id' => $equity->id, 'sort_order' => 33]);

        // -------------------------------------------------------
        // INCOME
        // -------------------------------------------------------
        $income = $create(['code' => '4000', 'name' => 'Income', 'account_type' => 'income', 'account_sub_type' => 'interest_income', 'normal_balance' => 'credit', 'sort_order' => 40, 'allow_manual_entry' => false]);

        $create(['code' => '4100', 'name' => 'Interest Income', 'account_type' => 'income', 'account_sub_type' => 'interest_income', 'normal_balance' => 'credit', 'parent_id' => $income->id, 'sort_order' => 41, 'allow_manual_entry' => false]);
        $create(['code' => '4200', 'name' => 'Processing Fee Income', 'account_type' => 'income', 'account_sub_type' => 'fee_income', 'normal_balance' => 'credit', 'parent_id' => $income->id, 'sort_order' => 42]);
        $create(['code' => '4300', 'name' => 'Penalty Income', 'account_type' => 'income', 'account_sub_type' => 'penalty_income', 'normal_balance' => 'credit', 'parent_id' => $income->id, 'sort_order' => 43, 'allow_manual_entry' => false]);
        $create(['code' => '4400', 'name' => 'Foreclosure Charges Income', 'account_type' => 'income', 'account_sub_type' => 'fee_income', 'normal_balance' => 'credit', 'parent_id' => $income->id, 'sort_order' => 44]);
        $create(['code' => '4500', 'name' => 'Other Income', 'account_type' => 'income', 'account_sub_type' => 'fee_income', 'normal_balance' => 'credit', 'parent_id' => $income->id, 'sort_order' => 45]);

        // -------------------------------------------------------
        // EXPENSES
        // -------------------------------------------------------
        $expense = $create(['code' => '5000', 'name' => 'Expenses', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'sort_order' => 50, 'allow_manual_entry' => false]);

        $create(['code' => '5100', 'name' => 'Interest Expense (on Borrowings)', 'account_type' => 'expense', 'account_sub_type' => 'interest_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 51]);
        $create(['code' => '5200', 'name' => 'Loan Loss Provision', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 52]);
        $create(['code' => '5300', 'name' => 'Salaries & Wages', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 53]);
        $create(['code' => '5400', 'name' => 'Rent & Office Expenses', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 54]);
        $create(['code' => '5500', 'name' => 'Bank Charges', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 55]);
        $create(['code' => '5600', 'name' => 'Waiver Written Off', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 56, 'allow_manual_entry' => false]);
        $create(['code' => '5700', 'name' => 'Write Off Losses', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 57, 'allow_manual_entry' => false]);
        $create(['code' => '5800', 'name' => 'Other Operating Expenses', 'account_type' => 'expense', 'account_sub_type' => 'operating_expense', 'normal_balance' => 'debit', 'parent_id' => $expense->id, 'sort_order' => 58]);

        $this->command->info('✅ Chart of Accounts seeded (' . ChartOfAccount::where('company_id', $cid)->count() . ' accounts).');
    }
}
