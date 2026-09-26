<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model {
    protected $fillable = ['company_id','group','key','value','type','label','description','is_public'];
    protected $casts = ['is_public'=>'boolean'];
    public function company() { return $this->belongsTo(Company::class); }
    public function getTypedValueAttribute(): mixed {
        return match($this->type) {
            'integer' => (int) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }
}
