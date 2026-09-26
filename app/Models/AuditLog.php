<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model {
    public $timestamps = false;
    protected $fillable = ['company_id','user_id','user_name','user_role','model_type','model_id','event','old_values','new_values','ip_address','user_agent','description','created_at'];
    protected $casts = ['old_values'=>'array','new_values'=>'array','created_at'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}
