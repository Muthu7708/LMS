<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerBankAccount extends Model {
    protected $fillable = ['customer_id','bank_name','branch_name','account_number','account_holder_name','account_type','ifsc_code','micr_code','is_primary','nach_mandate','mandate_registered_date','mandate_expiry_date'];
    protected $casts = ['is_primary'=>'boolean','nach_mandate'=>'boolean','mandate_registered_date'=>'date','mandate_expiry_date'=>'date'];
    public function customer() { return $this->belongsTo(Customer::class); }
}
