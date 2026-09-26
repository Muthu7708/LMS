<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanApproval extends Model {
    protected $fillable = ['loan_id','approved_by','approval_level','approver_role','action','approved_amount','approved_rate','approved_tenure','conditions','remarks','actioned_at'];
    protected $casts = ['actioned_at' => 'datetime', 'approved_amount' => 'decimal:2', 'approved_rate' => 'decimal:4'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
}
