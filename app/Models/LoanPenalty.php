<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanPenalty extends Model {
    protected $fillable = ['loan_id','emi_schedule_id','penalty_date','overdue_days','overdue_amount','penalty_rate','penalty_amount','paid_amount','waived_amount','status'];
    protected $casts = ['penalty_date'=>'date','penalty_amount'=>'decimal:2','paid_amount'=>'decimal:2','overdue_amount'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function emiSchedule() { return $this->belongsTo(LoanEmiSchedule::class); }
}
