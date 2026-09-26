<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DocumentVersion extends Model {
    protected $fillable = ['document_id','uploaded_by','version_number','file_path','file_name','file_type','file_size','file_hash','is_current','change_notes'];
    protected $casts = ['is_current'=>'boolean'];
    public function document() { return $this->belongsTo(Document::class); }
    public function uploadedBy() { return $this->belongsTo(User::class,'uploaded_by'); }
    public function getUrlAttribute(): string { return asset('storage/'.$this->file_path); }
    public function getFileSizeHumanAttribute(): string {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes/1048576,2).' MB';
        if ($bytes >= 1024) return round($bytes/1024,2).' KB';
        return $bytes.' B';
    }
}
