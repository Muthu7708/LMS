<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $totalAssets      = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'asset')->count();
        $totalLiabilities = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'liability')->count();
        $totalIncome      = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'income')->count();
        $totalExpenses    = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'expense')->count();
        $recentJournals   = JournalEntry::where('company_id', $companyId)->latest()->limit(10)->get();

        return view('accounting.index', compact('totalAssets', 'totalLiabilities', 'totalIncome', 'totalExpenses', 'recentJournals'));
    }

    public function coa()
    {
        $accounts = ChartOfAccount::where('company_id', Auth::user()->company_id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('accounting.coa', compact('accounts'));
    }

    public function storeCoa(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:20|unique:chart_of_accounts,code',
            'name'           => 'required|string|max:150',
            'account_type'   => 'required|in:asset,liability,equity,income,expense',
            'account_sub_type'=> 'required|string',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:chart_of_accounts,id',
            'description'    => 'nullable|string',
        ]);

        ChartOfAccount::create([
            ...$data,
            'company_id'          => Auth::user()->company_id,
            'is_system_account'   => false,
            'allow_manual_entry'  => true,
            'is_active'           => true,
        ]);

        return back()->with('success', 'Account added to Chart of Accounts.');
    }

    public function journal()
    {
        $journals = JournalEntry::where('company_id', Auth::user()->company_id)
            ->with(['lines.account', 'createdBy'])
            ->latest()
            ->paginate(20);

        return view('accounting.journal', compact('journals'));
    }

    public function createJournal()
    {
        $accounts = ChartOfAccount::where('company_id', Auth::user()->company_id)->active()->get();
        return view('accounting.create-journal', compact('accounts'));
    }

    public function storeJournal(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'narration'  => 'required|string',
            'lines'      => 'required|array|min:2',
            'lines.*.account_id'  => 'required|exists:chart_of_accounts,id',
            'lines.*.type'        => 'required|in:debit,credit',
            'lines.*.amount'      => 'required|numeric|min:0.01',
            'lines.*.description' => 'nullable|string',
        ]);

        // Validate double entry balance (Total Debits == Total Credits)
        $totalDebit  = 0;
        $totalCredit = 0;
        foreach ($request->lines as $line) {
            if ($line['type'] === 'debit')  $totalDebit  += (float)$line['amount'];
            if ($line['type'] === 'credit') $totalCredit += (float)$line['amount'];
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'Double Entry Unbalanced! Total Debits (₹' . number_format($totalDebit, 2) . ') must equal Total Credits (₹' . number_format($totalCredit, 2) . ').');
        }

        DB::transaction(function () use ($request, $totalDebit, $totalCredit) {
            $journal = JournalEntry::create([
                'company_id'  => Auth::user()->company_id,
                'branch_id'   => Auth::user()->branch_id,
                'created_by'  => Auth::id(),
                'journal_no'  => 'JV-' . time(),
                'entry_date'  => $request->entry_date,
                'narration'   => $request->narration,
                'total_debit' => $totalDebit,
                'total_credit'=> $totalCredit,
                'status'      => 'posted',
                'posted_by'   => Auth::id(),
                'posted_at'   => now(),
            ]);

            foreach ($request->lines as $l) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $l['account_id'],
                    'type'             => $l['type'],
                    'amount'           => $l['amount'],
                    'description'      => $l['description'] ?? null,
                ]);
            }
        });

        return redirect()->route('accounting.journal')->with('success', 'Journal entry posted successfully.');
    }

    public function postJournal(JournalEntry $entry)
    {
        $entry->update(['status' => 'posted', 'posted_by' => Auth::id(), 'posted_at' => now()]);
        return back()->with('success', 'Journal entry posted.');
    }

    public function ledger(Request $request)
    {
        $accounts  = ChartOfAccount::where('company_id', Auth::user()->company_id)->get();
        $selectedAccount = null;
        $lines     = collect();

        if ($request->account_id) {
            $selectedAccount = ChartOfAccount::find($request->account_id);
            $lines = JournalEntryLine::where('account_id', $request->account_id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
                ->with('journalEntry')
                ->get();
        }

        return view('accounting.ledger', compact('accounts', 'selectedAccount', 'lines'));
    }

    public function trialBalance()
    {
        $accounts = ChartOfAccount::where('company_id', Auth::user()->company_id)
            ->with(['journalLines' => fn ($q) => $q->whereHas('journalEntry', fn ($j) => $j->where('status', 'posted'))])
            ->get();

        return view('accounting.trial-balance', compact('accounts'));
    }

    public function profitLoss()
    {
        $companyId = Auth::user()->company_id;
        $incomeAccounts = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'income')->get();
        $expenseAccounts = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'expense')->get();

        return view('accounting.profit-loss', compact('incomeAccounts', 'expenseAccounts'));
    }

    public function balanceSheet()
    {
        $companyId = Auth::user()->company_id;
        $assetAccounts     = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'asset')->get();
        $liabilityAccounts = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'liability')->get();
        $equityAccounts    = ChartOfAccount::where('company_id', $companyId)->where('account_type', 'equity')->get();

        return view('accounting.balance-sheet', compact('assetAccounts', 'liabilityAccounts', 'equityAccounts'));
    }
}
