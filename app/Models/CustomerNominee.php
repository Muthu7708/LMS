<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerNominee extends Model {
    protected $fillable = ['customer_id','name','relationship','date_of_birth','phone','address','share_percent','is_minor','guardian_name'];
    protected $casts = ['date_of_birth'=>'date','is_minor'=>'boolean','share_percent'=>'decimal:2'];
    public function customer() { return $this->belongsTo(Customer::class); }
}
