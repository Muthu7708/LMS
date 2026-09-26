<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\LoanController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\EmiController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\AuditController;
use App\Http\Controllers\Web\AccountingController;
use App\Http\Controllers\Web\DocumentController;
use App\Http\Controllers\Web\NotificationController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/',        [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login.form');
    Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── DASHBOARD ────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/kpi-data', [DashboardController::class, 'kpiData'])->name('dashboard.kpi');

    // ── ORGANIZATION ─────────────────────────────────────────────────────
    Route::prefix('branches')->name('branches.')->middleware('permission:branch.view')->group(function () {
        Route::get('/',           [BranchController::class, 'index'])->name('index');
        Route::get('/create',     [BranchController::class, 'create'])->name('create')->middleware('permission:branch.create');
        Route::post('/',          [BranchController::class, 'store'])->name('store')->middleware('permission:branch.create');
        Route::get('/{branch}',   [BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit')->middleware('permission:branch.edit');
        Route::put('/{branch}',   [BranchController::class, 'update'])->name('update')->middleware('permission:branch.edit');
        Route::delete('/{branch}',[BranchController::class, 'destroy'])->name('destroy')->middleware('permission:branch.delete');
    });

    // ── USERS & ROLES ─────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->middleware('permission:user.view')->group(function () {
        Route::get('/',             [UserController::class, 'index'])->name('index');
        Route::get('/create',       [UserController::class, 'create'])->name('create')->middleware('permission:user.create');
        Route::post('/',            [UserController::class, 'store'])->name('store')->middleware('permission:user.create');
        Route::get('/{user}',       [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit',  [UserController::class, 'edit'])->name('edit')->middleware('permission:user.edit');
        Route::put('/{user}',       [UserController::class, 'update'])->name('update')->middleware('permission:user.edit');
        Route::delete('/{user}',    [UserController::class, 'destroy'])->name('destroy')->middleware('permission:user.delete');
        Route::patch('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active')->middleware('permission:user.edit');
    });

    // ── CUSTOMERS ─────────────────────────────────────────────────────────
    Route::prefix('customers')->name('customers.')->middleware('permission:customer.view')->group(function () {
        Route::get('/',                        [CustomerController::class, 'index'])->name('index');
        Route::get('/create',                  [CustomerController::class, 'create'])->name('create')->middleware('permission:customer.create');
        Route::post('/',                       [CustomerController::class, 'store'])->name('store')->middleware('permission:customer.create');
        Route::get('/{customer}',              [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit',         [CustomerController::class, 'edit'])->name('edit')->middleware('permission:customer.edit');
        Route::put('/{customer}',              [CustomerController::class, 'update'])->name('update')->middleware('permission:customer.edit');
        Route::delete('/{customer}',           [CustomerController::class, 'destroy'])->name('destroy')->middleware('permission:customer.delete');
        Route::post('/{customer}/blacklist',   [CustomerController::class, 'blacklist'])->name('blacklist')->middleware('permission:customer.blacklist');
        Route::post('/{customer}/unblacklist', [CustomerController::class, 'unblacklist'])->name('unblacklist')->middleware('permission:customer.unblacklist');
        // Sub-resources
        Route::post('/{customer}/addresses',   [CustomerController::class, 'storeAddress'])->name('addresses.store');
        Route::post('/{customer}/kyc',         [CustomerController::class, 'storeKyc'])->name('kyc.store');
        Route::post('/{customer}/employment',  [CustomerController::class, 'storeEmployment'])->name('employment.store');
        Route::post('/{customer}/bank-accounts', [CustomerController::class, 'storeBankAccount'])->name('bank-accounts.store');
        Route::post('/{customer}/nominees',    [CustomerController::class, 'storeNominee'])->name('nominees.store');
        Route::post('/{customer}/references',  [CustomerController::class, 'storeReference'])->name('references.store');
    });

    // ── LOANS ─────────────────────────────────────────────────────────────
    Route::prefix('loans')->name('loans.')->middleware('permission:loan.view')->group(function () {
        Route::get('/',                         [LoanController::class, 'index'])->name('index');
        Route::get('/create',                   [LoanController::class, 'create'])->name('create')->middleware('permission:loan.create');
        Route::post('/',                        [LoanController::class, 'store'])->name('store')->middleware('permission:loan.create');
        Route::get('/{loan}',                   [LoanController::class, 'show'])->name('show');
        Route::get('/{loan}/edit',              [LoanController::class, 'edit'])->name('edit')->middleware('permission:loan.edit');
        Route::put('/{loan}',                   [LoanController::class, 'update'])->name('update')->middleware('permission:loan.edit');
        // Workflow transitions
        Route::post('/{loan}/submit',           [LoanController::class, 'submit'])->name('submit')->middleware('permission:loan.submit');
        Route::post('/{loan}/verify',           [LoanController::class, 'verify'])->name('verify')->middleware('permission:loan.verify');
        Route::post('/{loan}/approve',          [LoanController::class, 'approve'])->name('approve')->middleware('permission:loan.approve');
        Route::post('/{loan}/reject',           [LoanController::class, 'reject'])->name('reject')->middleware('permission:loan.reject');
        Route::post('/{loan}/disburse',         [LoanController::class, 'disburse'])->name('disburse')->middleware('permission:loan.disburse');
        Route::post('/{loan}/close',            [LoanController::class, 'close'])->name('close')->middleware('permission:loan.close');
        // EMI Schedule
        Route::get('/{loan}/emi-schedule',      [EmiController::class, 'show'])->name('emi-schedule');
        Route::post('/simulate',                [EmiController::class, 'simulate'])->name('simulate');
        // Payments
        Route::get('/{loan}/payments',          [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/{loan}/payments/collect',  [PaymentController::class, 'create'])->name('payments.create')->middleware('permission:payment.collect');
        Route::post('/{loan}/payments',         [PaymentController::class, 'store'])->name('payments.store')->middleware('permission:payment.collect');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::post('/payments/{payment}/reprint', [PaymentController::class, 'reprint'])->name('payments.reprint')->middleware('permission:payment.receipt.reprint');
        Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse')->middleware('permission:payment.reverse');
        // Collateral & Guarantors
        Route::post('/{loan}/collaterals',      [LoanController::class, 'storeCollateral'])->name('collaterals.store');
        Route::post('/{loan}/guarantors',       [LoanController::class, 'storeGuarantor'])->name('guarantors.store');
        // Waivers
        Route::post('/{loan}/waivers',          [LoanController::class, 'requestWaiver'])->name('waivers.request')->middleware('permission:waiver.request');
        Route::post('/waivers/{waiver}/approve', [LoanController::class, 'approveWaiver'])->name('waivers.approve')->middleware('permission:waiver.approve');
        // Restructure
        Route::post('/{loan}/restructure',      [LoanController::class, 'restructure'])->name('restructure')->middleware('permission:loan.restructure');
        // Foreclosure
        Route::get('/{loan}/foreclosure',       [LoanController::class, 'foreclosureForm'])->name('foreclosure.form')->middleware('permission:loan.foreclosure');
        Route::post('/{loan}/foreclosure',      [LoanController::class, 'foreclosure'])->name('foreclosure')->middleware('permission:loan.foreclosure');
    });

    // ── ACCOUNTING ────────────────────────────────────────────────────────
    Route::prefix('accounting')->name('accounting.')->middleware('permission:accounting.view')->group(function () {
        Route::get('/',                          [AccountingController::class, 'index'])->name('index');
        Route::get('/chart-of-accounts',         [AccountingController::class, 'coa'])->name('coa');
        Route::post('/chart-of-accounts',        [AccountingController::class, 'storeCoa'])->name('coa.store')->middleware('permission:accounting.coa.manage');
        Route::get('/journal',                   [AccountingController::class, 'journal'])->name('journal');
        Route::get('/journal/create',            [AccountingController::class, 'createJournal'])->name('journal.create')->middleware('permission:accounting.journal.create');
        Route::post('/journal',                  [AccountingController::class, 'storeJournal'])->name('journal.store')->middleware('permission:accounting.journal.create');
        Route::post('/journal/{entry}/post',     [AccountingController::class, 'postJournal'])->name('journal.post')->middleware('permission:accounting.journal.post');
        Route::get('/ledger',                    [AccountingController::class, 'ledger'])->name('ledger');
        Route::get('/trial-balance',             [AccountingController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/profit-loss',               [AccountingController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/balance-sheet',             [AccountingController::class, 'balanceSheet'])->name('balance-sheet');
    });

    // ── DOCUMENTS ────────────────────────────────────────────────────────
    Route::prefix('documents')->name('documents.')->middleware('permission:document.view')->group(function () {
        Route::get('/',                    [DocumentController::class, 'index'])->name('index');
        Route::post('/upload',             [DocumentController::class, 'upload'])->name('upload')->middleware('permission:document.upload');
        Route::get('/{document}',          [DocumentController::class, 'show'])->name('show');
        Route::post('/{document}/version', [DocumentController::class, 'addVersion'])->name('version')->middleware('permission:document.upload');
        Route::post('/{document}/approve', [DocumentController::class, 'approve'])->name('approve')->middleware('permission:document.approve');
        Route::get('/{version}/download',  [DocumentController::class, 'download'])->name('download');
    });

    // ── REPORTS ──────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->middleware('permission:report.loan')->group(function () {
        Route::get('/',                    [ReportController::class, 'index'])->name('index');
        Route::get('/disbursement',        [ReportController::class, 'disbursement'])->name('disbursement');
        Route::get('/collection',          [ReportController::class, 'collection'])->name('collection');
        Route::get('/overdue',             [ReportController::class, 'overdue'])->name('overdue');
        Route::get('/portfolio',           [ReportController::class, 'portfolio'])->name('portfolio');
        Route::get('/customer-statement',  [ReportController::class, 'customerStatement'])->name('customer-statement');
        Route::get('/npa',                 [ReportController::class, 'npa'])->name('npa');
        // Exports
        Route::get('/disbursement/pdf',    [ReportController::class, 'disbursementPdf'])->name('disbursement.pdf');
        Route::get('/disbursement/excel',  [ReportController::class, 'disbursementExcel'])->name('disbursement.excel');
        Route::get('/collection/pdf',      [ReportController::class, 'collectionPdf'])->name('collection.pdf');
        Route::get('/collection/excel',    [ReportController::class, 'collectionExcel'])->name('collection.excel');
        Route::get('/overdue/pdf',         [ReportController::class, 'overduePdf'])->name('overdue.pdf');
        Route::get('/overdue/excel',       [ReportController::class, 'overdueExcel'])->name('overdue.excel');
        Route::get('/customer-statement/{customer}/pdf', [ReportController::class, 'customerStatementPdf'])->name('customer-statement.pdf');
    });

    // ── NOTIFICATIONS ─────────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->middleware('permission:notification.view')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{log}', [NotificationController::class, 'show'])->name('show');
    });

    // ── AUDIT LOGS ────────────────────────────────────────────────────────
    Route::prefix('audit')->name('audit.')->middleware('permission:audit.view')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
    });

    // ── SETTINGS ──────────────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/',       [SettingController::class, 'index'])->name('index');
        Route::put('/update', [SettingController::class, 'update'])->name('update')->middleware('permission:settings.edit');
    });

    // ── PROFILE ──────────────────────────────────────────────────────────
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

});
