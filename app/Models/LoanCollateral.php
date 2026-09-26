<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LoanCollateral extends Model {
    protected $fillable = ['loan_id','collateral_type','description','owner_name','estimated_value','market_value','valuation_by','valuation_date','insurance_policy_no','insurance_expiry','address','registration_no','gold_weight_grams','gold_purity_karats','is_released','released_date'];
    protected $casts = ['valuation_date'=>'date','insurance_expiry'=>'date','released_date'=>'date','is_released'=>'boolean','estimated_value'=>'decimal:2','market_value'=>'decimal:2'];
    public function loan() { return $this->belongsTo(Loan::class); }
}
