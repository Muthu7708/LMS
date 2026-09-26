<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanForeclosure extends Model {
    protected $fillable = ['loan_id','requested_by','approved_by','request_date','foreclosure_date','outstanding_principal','outstanding_interest','outstanding_penalty','foreclosure_charges','total_foreclosure_amount','amount_paid','payment_mode','payment_reference','status','remarks'];
    protected $casts = ['request_date'=>'date','foreclosure_date'=>'date','total_foreclosure_amount'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function requestedBy() { return $this->belongsTo(User::class,'requested_by'); }
}
