<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanDisbursement extends Model {
    protected $fillable = ['loan_id','disbursed_by','disbursement_no','tranche_number','amount','mode','reference_no','bank_name','account_number','disbursement_date','remarks'];
    protected $casts = ['disbursement_date'=>'date','amount'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function disbursedBy() { return $this->belongsTo(User::class,'disbursed_by'); }
}
