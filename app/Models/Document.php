<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Document extends Model {
    use SoftDeletes;
    protected $fillable = ['company_id','documentable_type','documentable_id','uploaded_by','document_type','document_category','title','description','status','reviewed_by','reviewed_at','review_remarks','valid_from','valid_until','is_verified'];
    protected $casts = ['reviewed_at'=>'datetime','valid_from'=>'date','valid_until'=>'date','is_verified'=>'boolean'];
    public function documentable() { return $this->morphTo(); }
    public function uploadedBy() { return $this->belongsTo(User::class,'uploaded_by'); }
    public function versions() { return $this->hasMany(DocumentVersion::class)->orderBy('version_number','desc'); }
    public function currentVersion() { return $this->hasOne(DocumentVersion::class)->where('is_current',true); }
}
