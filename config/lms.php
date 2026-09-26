<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LMS — Finance ERP Configuration
    |--------------------------------------------------------------------------
    */

    'currency' => env('LMS_CURRENCY', 'INR'),
    'currency_symbol' => env('LMS_CURRENCY_SYMBOL', '₹'),
    'date_format' => env('LMS_DATE_FORMAT', 'd/m/Y'),
    'timezone' => env('LMS_TIMEZONE', 'Asia/Kolkata'),
    'max_upload_mb' => env('LMS_MAX_UPLOAD_MB', 10),

    /*
    |--------------------------------------------------------------------------
    | Loan Types
    |--------------------------------------------------------------------------
    */
    'loan_types' => [
        'personal'  => 'Personal Loan',
        'business'  => 'Business / MSME Loan',
        'vehicle'   => 'Vehicle Loan',
        'gold'      => 'Gold Loan',
        'home'      => 'Home / Mortgage Loan',
        'education' => 'Education Loan',
        'agriculture' => 'Agriculture Loan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Interest Calculation Methods
    |--------------------------------------------------------------------------
    */
    'interest_methods' => [
        'flat'              => 'Flat Rate',
        'reducing'          => 'Reducing Balance (Monthly)',
        'reducing_daily'    => 'Reducing Balance (Daily)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Frequencies
    |--------------------------------------------------------------------------
    */
    'payment_frequencies' => [
        'monthly'   => 'Monthly',
        'weekly'    => 'Weekly',
        'biweekly'  => 'Bi-Weekly (Fortnightly)',
        'quarterly' => 'Quarterly',
        'annual'    => 'Annual',
        'bullet'    => 'Bullet (Lump-sum at end)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Loan Workflow States
    |--------------------------------------------------------------------------
    */
    'loan_statuses' => [
        'draft'         => 'Draft',
        'submitted'     => 'Submitted',
        'under_review'  => 'Under Review',
        'verified'      => 'Verified',
        'approved'      => 'Approved',
        'rejected'      => 'Rejected',
        'agreement'     => 'Agreement Signed',
        'disbursed'     => 'Disbursed',
        'active'        => 'Active',
        'overdue'       => 'Overdue',
        'npa'           => 'NPA (Non-Performing Asset)',
        'restructured'  => 'Restructured',
        'foreclosed'    => 'Foreclosed',
        'settled'       => 'Settled',
        'closed'        => 'Closed',
        'written_off'   => 'Written Off',
        'blacklisted'   => 'Blacklisted',
    ],

    /*
    |--------------------------------------------------------------------------
    | Disbursement Modes
    |--------------------------------------------------------------------------
    */
    'disbursement_modes' => [
        'cash'      => 'Cash',
        'bank_transfer' => 'Bank Transfer (NEFT/RTGS/IMPS)',
        'cheque'    => 'Cheque',
        'upi'       => 'UPI',
        'dd'        => 'Demand Draft',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Modes (for EMI collections)
    |--------------------------------------------------------------------------
    */
    'payment_modes' => [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque'        => 'Cheque',
        'upi'           => 'UPI',
        'online'        => 'Online Portal',
        'auto_debit'    => 'Auto Debit (ECS/NACH)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    */
    'document_types' => [
        'aadhaar'       => 'Aadhaar Card',
        'pan'           => 'PAN Card',
        'passport'      => 'Passport',
        'driving_licence' => 'Driving Licence',
        'voter_id'      => 'Voter ID',
        'utility_bill'  => 'Utility Bill',
        'bank_statement'=> 'Bank Statement (6 months)',
        'itr'           => 'Income Tax Return',
        'salary_slip'   => 'Salary Slip (3 months)',
        'form_16'       => 'Form 16',
        'property_doc'  => 'Property Document',
        'business_proof'=> 'Business Proof',
        'photo'         => 'Photograph',
        'signature'     => 'Signature',
        'other'         => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | KYC Document Categories
    |--------------------------------------------------------------------------
    */
    'kyc_types' => [
        'identity'  => 'Identity Proof',
        'address'   => 'Address Proof',
        'income'    => 'Income Proof',
        'photo'     => 'Photograph',
        'signature' => 'Signature',
    ],

    /*
    |--------------------------------------------------------------------------
    | Employment Types
    |--------------------------------------------------------------------------
    */
    'employment_types' => [
        'salaried'          => 'Salaried (Private)',
        'salaried_govt'     => 'Salaried (Government)',
        'self_employed'     => 'Self Employed (Professional)',
        'business'          => 'Business Owner',
        'agriculture'       => 'Agriculturist / Farmer',
        'retired'           => 'Retired / Pensioner',
        'homemaker'         => 'Homemaker',
        'student'           => 'Student',
        'unemployed'        => 'Unemployed',
        'other'             => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Types (Chart of Accounts)
    |--------------------------------------------------------------------------
    */
    'account_types' => [
        'asset'     => 'Asset',
        'liability' => 'Liability',
        'equity'    => 'Equity',
        'income'    => 'Income',
        'expense'   => 'Expense',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prepayment Handling
    |--------------------------------------------------------------------------
    */
    'prepayment_options' => [
        'reduce_tenure' => 'Reduce Tenure (keep EMI same)',
        'reduce_emi'    => 'Reduce EMI (keep tenure same)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Thresholds (INR)
    |--------------------------------------------------------------------------
    */
    'approval_thresholds' => [
        'loan_officer'      => 100000,   // Up to 1 Lakh — Loan Officer can approve
        'branch_manager'    => 1000000,  // Up to 10 Lakhs — Branch Manager
        'company_admin'     => PHP_INT_MAX, // Any amount — Company Admin
    ],

    /*
    |--------------------------------------------------------------------------
    | Penalty Settings
    |--------------------------------------------------------------------------
    */
    'penalty' => [
        'rate_per_day_percent' => 0.05,   // 0.05% per day on overdue
        'grace_days'           => 3,       // No penalty for first 3 days after due
    ],

    /*
    |--------------------------------------------------------------------------
    | NPA Classification (days overdue)
    |--------------------------------------------------------------------------
    */
    'npa' => [
        'sub_standard_days' => 90,   // 90+ days — Sub-Standard
        'doubtful_days'     => 180,  // 180+ days — Doubtful
        'loss_days'         => 365,  // 365+ days — Loss
    ],
];
