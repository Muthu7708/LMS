<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerEmployment extends Model
{
    protected $table = 'customer_employment';

    protected $fillable = [
        'customer_id', 'employment_type', 'employer_name', 'designation', 'department',
        'joining_date', 'years_of_experience', 'monthly_income', 'other_income',
        'income_source', 'office_address', 'office_phone', 'is_current'
    ];

    protected $casts = [
        'joining_date'   => 'date',
        'is_current'     => 'boolean',
        'monthly_income' => 'decimal:2',
        'other_income'   => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getTotalIncomeAttribute(): float
    {
        return (float) $this->monthly_income + (float) $this->other_income;
    }
}
