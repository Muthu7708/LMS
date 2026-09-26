<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerKyc extends Model { protected $table = 'customer_kyc';
    protected $fillable = ['customer_id','document_type','document_category','document_number','issue_date','expiry_date','issuing_authority','status','rejection_reason','verified_by','verified_at'];
    protected $casts = ['issue_date'=>'date','expiry_date'=>'date','verified_at'=>'datetime'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function verifiedBy() { return $this->belongsTo(User::class,'verified_by'); }
}
