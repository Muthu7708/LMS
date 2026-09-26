<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanGuarantor extends Model {
    protected $fillable = ['loan_id','customer_id','name','relationship','mobile','email','pan','aadhaar','monthly_income','address','employment_type','employer_name','has_signed','signed_date'];
    protected $casts = ['has_signed'=>'boolean','signed_date'=>'date','monthly_income'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
