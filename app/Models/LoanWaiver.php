<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanWaiver extends Model {
    protected $fillable = ['loan_id','requested_by','approved_by','waiver_type','requested_amount','approved_amount','reason','approver_remarks','status','actioned_at'];
    protected $casts = ['actioned_at'=>'datetime','requested_amount'=>'decimal:2','approved_amount'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function requestedBy() { return $this->belongsTo(User::class,'requested_by'); }
    public function approvedBy() { return $this->belongsTo(User::class,'approved_by'); }
}
