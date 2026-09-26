<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'created_by', 'customer_no',
        'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
        'father_name', 'mother_name', 'spouse_name', 'marital_status',
        'religion', 'caste', 'education',
        'mobile', 'alternate_mobile', 'email', 'whatsapp',
        'pan', 'aadhaar', 'voter_id', 'driving_licence', 'passport_no',
        'status', 'blacklist_reason', 'blacklisted_at', 'blacklisted_by',
        'credit_score', 'risk_category', 'last_score_updated_at', 'notes',
    ];

    protected $casts = [
        'date_of_birth'          => 'date',
        'blacklisted_at'         => 'datetime',
        'last_score_updated_at'  => 'datetime',
    ];

    // ---- Relationships ----

    public function company()   { return $this->belongsTo(Company::class); }
    public function branch()    { return $this->belongsTo(Branch::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function primaryAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_primary', true);
    }

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function kyc()
    {
        return $this->hasMany(CustomerKyc::class);
    }

    public function employment()
    {
        return $this->hasMany(CustomerEmployment::class)->where('is_current', true);
    }

    public function bankAccounts()
    {
        return $this->hasMany(CustomerBankAccount::class);
    }

    public function primaryBankAccount()
    {
        return $this->hasOne(CustomerBankAccount::class)->where('is_primary', true);
    }

    public function nominees()
    {
        return $this->hasMany(CustomerNominee::class);
    }

    public function references()
    {
        return $this->hasMany(CustomerReference::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans()
    {
        return $this->hasMany(Loan::class)->whereIn('status', ['active', 'overdue', 'npa', 'restructured']);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function guarantors()
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    // ---- Scopes ----

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlacklisted($query)
    {
        return $query->where('status', 'blacklisted');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('customer_no', 'ilike', "%{$term}%")
              ->orWhere('mobile', 'like', "%{$term}%")
              ->orWhere('pan', 'ilike', "%{$term}%")
              ->orWhereRaw("full_name ilike ?", ["%{$term}%"]);
        });
    }

    // ---- Helpers ----

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function isBlacklisted(): bool
    {
        return $this->status === 'blacklisted';
    }

    public function getRiskBadgeColorAttribute(): string
    {
        return match($this->risk_category) {
            'low'       => 'success',
            'medium'    => 'info',
            'high'      => 'warning',
            'very_high' => 'danger',
            default     => 'secondary',
        };
    }
}
