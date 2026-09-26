<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanVerification extends Model {
    protected $fillable = ['loan_id','verified_by','verification_type','status','remarks','checklist','gps_coordinates','visited_at'];
    protected $casts = ['checklist' => 'array', 'visited_at' => 'datetime'];
    public function loan() { return $this->belongsTo(Loan::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, 'verified_by'); }
}
