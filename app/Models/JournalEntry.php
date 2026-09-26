<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JournalEntry extends Model {
    protected $fillable = ['company_id','branch_id','created_by','journal_no','entry_date','reference_type','reference_id','narration','total_debit','total_credit','status','posted_by','posted_at','reversed_by','reversed_at','reversal_of'];
    protected $casts = ['entry_date'=>'date','posted_at'=>'datetime','reversed_at'=>'datetime','total_debit'=>'decimal:2','total_credit'=>'decimal:2'];
    public function company() { return $this->belongsTo(Company::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function lines() { return $this->hasMany(JournalEntryLine::class); }
    public function createdBy() { return $this->belongsTo(User::class,'created_by'); }
}
