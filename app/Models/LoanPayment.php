<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class LoanPayment extends Model {
    use SoftDeletes;
    protected $fillable = ['loan_id','branch_id','collected_by','receipt_no','payment_date','amount','principal_paid','interest_paid','penalty_paid','excess_amount','payment_mode','reference_no','bank_name','cheque_date','remarks','is_reversed','reversed_at','reversed_by','reversal_reason'];
    protected $casts = ['payment_date'=>'date','cheque_date'=>'date','is_reversed'=>'boolean','reversed_at'=>'datetime','amount'=>'decimal:2','principal_paid'=>'decimal:2','interest_paid'=>'decimal:2','penalty_paid'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function collectedBy() { return $this->belongsTo(User::class, 'collected_by'); }
    public function reprints() { return $this->hasMany(ReceiptReprint::class, 'payment_id'); }
}
