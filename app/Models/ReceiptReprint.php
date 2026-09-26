<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ReceiptReprint extends Model {
    public $timestamps = false;
    protected $fillable = ['payment_id','reprinted_by','receipt_no','reason','ip_address','reprinted_at'];
    protected $casts = ['reprinted_at'=>'datetime'];
    public function payment() { return $this->belongsTo(LoanPayment::class,'payment_id'); }
    public function reprintedBy() { return $this->belongsTo(User::class,'reprinted_by'); }
}
