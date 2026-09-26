<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'loan_officer_id',
        'loan_no', 'loan_type', 'purpose',
        'applied_amount', 'approved_amount', 'disbursed_amount',
        'tenure_months', 'interest_rate', 'interest_method', 'payment_frequency',
        'processing_fee', 'processing_fee_gst', 'insurance_amount', 'other_charges',
        'grace_period_months', 'moratorium_months', 'prepayment_option',
        'application_date', 'approved_date', 'disbursement_date', 'first_emi_date',
        'last_emi_date', 'maturity_date', 'closed_date',
        'emi_amount', 'total_emis', 'total_interest_payable', 'total_amount_payable',
        'outstanding_principal', 'outstanding_interest', 'outstanding_penalty',
        'total_paid', 'total_paid_principal', 'total_paid_interest', 'total_paid_penalty',
        'emis_paid', 'emis_overdue',
        'status', 'rejection_reason', 'is_restructured', 'is_npa', 'npa_date',
        'npa_category', 'is_renewed', 'renewed_from_loan_id', 'internal_notes',
    ];

    protected $casts = [
        'application_date'    => 'date',
        'approved_date'       => 'date',
        'disbursement_date'   => 'date',
        'first_emi_date'      => 'date',
        'last_emi_date'       => 'date',
        'maturity_date'       => 'date',
        'closed_date'         => 'date',
        'npa_date'            => 'date',
        'is_restructured'     => 'boolean',
        'is_npa'              => 'boolean',
        'is_renewed'          => 'boolean',
        'applied_amount'      => 'decimal:2',
        'approved_amount'     => 'decimal:2',
        'disbursed_amount'    => 'decimal:2',
        'interest_rate'       => 'decimal:4',
        'emi_amount'          => 'decimal:2',
        'outstanding_principal' => 'decimal:2',
        'outstanding_interest'  => 'decimal:2',
        'outstanding_penalty'   => 'decimal:2',
        'total_paid'            => 'decimal:2',
    ];

    // ---- Relationships ----

    public function company()     { return $this->belongsTo(Company::class); }
    public function branch()      { return $this->belongsTo(Branch::class); }
    public function customer()    { return $this->belongsTo(Customer::class); }
    public function loanOfficer() { return $this->belongsTo(User::class, 'loan_officer_id'); }

    public function verifications()
    {
        return $this->hasMany(LoanVerification::class);
    }

    public function approvals()
    {
        return $this->hasMany(LoanApproval::class)->orderBy('approval_level');
    }

    public function collaterals()
    {
        return $this->hasMany(LoanCollateral::class);
    }

    public function guarantors()
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    public function emiSchedules()
    {
        return $this->hasMany(LoanEmiSchedule::class)->orderBy('emi_number');
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class)->orderBy('payment_date', 'desc');
    }

    public function penalties()
    {
        return $this->hasMany(LoanPenalty::class);
    }

    public function waivers()
    {
        return $this->hasMany(LoanWaiver::class);
    }

    public function restructureHistory()
    {
        return $this->hasMany(LoanRestructureHistory::class)->orderBy('restructure_sequence');
    }

    public function foreclosure()
    {
        return $this->hasOne(LoanForeclosure::class)->latest();
    }

    public function settlement()
    {
        return $this->hasOne(LoanSettlement::class)->latest();
    }

    public function disbursements()
    {
        return $this->hasMany(LoanDisbursement::class)->orderBy('tranche_number');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function renewedFrom()
    {
        return $this->belongsTo(Loan::class, 'renewed_from_loan_id');
    }

    // ---- Scopes ----

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'overdue', 'npa', 'restructured']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ---- Helpers ----

    public function getStatusLabelAttribute(): string
    {
        return LoanStatus::from($this->status)->label();
    }

    public function getStatusColorAttribute(): string
    {
        return LoanStatus::from($this->status)->color();
    }

    public function getInterestTypeAttribute(): string
    {
        return $this->attributes['interest_method'] ?? 'reducing';
    }

    public function getTotalOutstandingAttribute(): float
    {
        return (float)$this->outstanding_principal
             + (float)$this->outstanding_interest
             + (float)$this->outstanding_penalty;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $current = LoanStatus::from($this->status);
        $new     = LoanStatus::from($newStatus);
        return $current->canTransitionTo($new);
    }

    public function isDisbursed(): bool
    {
        return in_array($this->status, ['disbursed', 'active', 'overdue', 'npa', 'restructured']);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'submitted', 'under_review']);
    }
}
