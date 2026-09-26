<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerReference extends Model {
    protected $fillable = ['customer_id','name','relationship','phone','email','address','occupation'];
    public function customer() { return $this->belongsTo(Customer::class); }
}
