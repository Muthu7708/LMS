<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CustomerContact extends Model {
    protected $fillable = ['customer_id','name','relationship','phone','email','is_emergency'];
    protected $casts = ['is_emergency' => 'boolean'];
    public function customer() { return $this->belongsTo(Customer::class); }
}
