<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'company_id', 'parent_id', 'code', 'name', 'account_type', 'account_sub_type',
        'normal_balance', 'is_system_account', 'is_bank_account', 'bank_name',
        'bank_account_no', 'allow_manual_entry', 'is_active', 'sort_order', 'description',
    ];

    protected $casts = [
        'is_system_account'   => 'boolean',
        'is_bank_account'     => 'boolean',
        'allow_manual_entry'  => 'boolean',
        'is_active'           => 'boolean',
    ];

    public function company()    { return $this->belongsTo(Company::class); }
    public function parent()     { return $this->belongsTo(ChartOfAccount::class, 'parent_id'); }
    public function children()   { return $this->hasMany(ChartOfAccount::class, 'parent_id')->orderBy('sort_order'); }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function scopeActive($query)  { return $query->where('is_active', true); }
    public function scopeOfType($query, string $type) { return $query->where('account_type', $type); }
}
