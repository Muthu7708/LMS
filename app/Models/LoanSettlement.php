<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanSettlement extends Model {
    protected $fillable = ['loan_id','settled_by','approved_by','outstanding_amount','settlement_amount','amount_waived','settlement_date','payment_mode','payment_reference','reason','remarks','status'];
    protected $casts = ['settlement_date'=>'date','settlement_amount'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
}
