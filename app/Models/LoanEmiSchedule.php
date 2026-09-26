<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanEmiSchedule extends Model {
    protected $fillable = ['loan_id','emi_number','due_date','emi_amount','principal_amount','interest_amount','opening_balance','closing_balance','paid_amount','paid_principal','paid_interest','paid_date','waived_amount','days_overdue','penalty_amount','paid_penalty','status','is_moratorium','is_grace'];
    protected $casts = ['due_date'=>'date','paid_date'=>'date','is_moratorium'=>'boolean','is_grace'=>'boolean','emi_amount'=>'decimal:2','principal_amount'=>'decimal:2','interest_amount'=>'decimal:2','paid_amount'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function isPaid(): bool { return $this->status === 'paid'; }
    public function isOverdue(): bool { return $this->status === 'overdue'; }
    public function getBalanceDueAttribute(): float { return max(0, (float)$this->emi_amount - (float)$this->paid_amount); }
}
