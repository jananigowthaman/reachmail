<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_name',
        'file_path',
        'file_name',
        'file_type',
        'mime_type',
        'file_size',
        'is_default_resume',
        'description',
    ];

    protected $casts = [
        'is_default_resume' => 'boolean',
        'file_size' => 'integer',
    ];

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
