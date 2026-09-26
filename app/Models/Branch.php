<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'address', 'city', 'state',
        'pin_code', 'phone', 'email', 'loan_limit', 'is_head_office', 'is_active',
    ];

    protected $casts = [
        'is_head_office' => 'boolean',
        'is_active'      => 'boolean',
        'loan_limit'     => 'decimal:2',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function users()     { return $this->hasMany(User::class); }
    public function customers() { return $this->hasMany(Customer::class); }
    public function loans()     { return $this->hasMany(Loan::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
