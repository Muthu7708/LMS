<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanEmiSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $today    = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Scope by branch if not super/company admin
        $branchId = ($user->hasRole(['super_admin','company_admin'])) ? null : $user->branch_id;

        $loanQuery    = Loan::query()->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $paymentQuery = LoanPayment::query()->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        // KPIs
        $kpis = [
            'total_active_loans'    => (clone $loanQuery)->whereIn('status', ['active','overdue','npa'])->count(),
            'total_disbursed_today' => (clone $loanQuery)->whereDate('disbursement_date', $today)->sum('disbursed_amount'),
            'total_collected_today' => (clone $paymentQuery)->whereDate('payment_date', $today)->sum('amount'),
            'total_overdue_loans'   => (clone $loanQuery)->where('status','overdue')->count(),
            'total_npa_loans'       => (clone $loanQuery)->where('status','npa')->count(),
            'total_customers'       => Customer::when($branchId, fn($q) => $q->where('branch_id',$branchId))->where('status','active')->count(),
            'total_portfolio'       => (clone $loanQuery)->whereIn('status',['active','overdue','npa'])->sum('outstanding_principal'),
            'collection_this_month' => (clone $paymentQuery)->where('payment_date', '>=', $thisMonth)->sum('amount'),
            // Pending approvals
            'pending_approval'      => (clone $loanQuery)->whereIn('status',['submitted','under_review','verified'])->count(),
            // Upcoming EMIs due today
            'emi_due_today'         => LoanEmiSchedule::whereDate('due_date', $today)
                                        ->whereIn('status',['pending','partial'])
                                        ->when($branchId, fn($q) => $q->whereHas('loan', fn($q2) => $q2->where('branch_id',$branchId)))
                                        ->count(),
        ];

        // Monthly disbursement trend (last 6 months)
        $disbursementTrend = Loan::select(
                DB::raw("TO_CHAR(disbursement_date, 'Mon YY') as month"),
                DB::raw('SUM(disbursed_amount) as total'),
                DB::raw("DATE_TRUNC('month', disbursement_date) as month_start")
            )
            ->whereNotNull('disbursement_date')
            ->where('disbursement_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy(DB::raw("TO_CHAR(disbursement_date, 'Mon YY'), DATE_TRUNC('month', disbursement_date)"))
            ->orderBy('month_start')
            ->get();

        // Loan status breakdown (pie chart)
        $statusBreakdown = Loan::select('status', DB::raw('count(*) as total'))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        // Recent loans
        $recentLoans = Loan::with(['customer', 'branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Overdue EMIs (top 10)
        $overdueEmis = LoanEmiSchedule::with(['loan.customer'])
            ->whereIn('status', ['pending','partial'])
            ->where('due_date', '<', $today)
            ->when($branchId, fn($q) => $q->whereHas('loan', fn($q2) => $q2->where('branch_id',$branchId)))
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'kpis', 'disbursementTrend', 'statusBreakdown', 'recentLoans', 'overdueEmis'
        ));
    }

    public function kpiData()
    {
        // Returns JSON for AJAX refresh
        return response()->json($this->index());
    }
}
