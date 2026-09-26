<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanRestructureHistory extends Model {
    protected $table = 'loan_restructure_history';
    protected $fillable = ['loan_id','restructured_by','approved_by','restructure_sequence','old_outstanding_principal','old_interest_rate','old_remaining_tenure','old_emi_amount','old_penalty_outstanding','new_outstanding_principal','new_interest_rate','new_tenure_months','new_emi_amount','new_first_emi_date','new_maturity_date','waived_penalty','capitalized_interest','reason','remarks','effective_date'];
    protected $casts = ['new_first_emi_date'=>'date','new_maturity_date'=>'date','effective_date'=>'datetime','old_emi_amount'=>'decimal:2','new_emi_amount'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function restructuredBy() { return $this->belongsTo(User::class,'restructured_by'); }
}
