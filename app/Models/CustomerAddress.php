<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerAddress extends Model {
    protected $fillable = ['customer_id','type','address_line1','address_line2','landmark','city','district','state','country','pin_code','is_primary','years_at_address','ownership'];
    protected $casts = ['is_primary' => 'boolean'];
    public function customer() { return $this->belongsTo(Customer::class); }
}
