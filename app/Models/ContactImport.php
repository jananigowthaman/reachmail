<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'original_filename',
        'total_rows',
        'imported_count',
        'updated_count',
        'skipped_count',
        'invalid_count',
        'status',
        'duplicate_mode',
        'column_mapping',
        'error_log',
        'target_category_id',
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'error_log' => 'array',
    ];

    public function targetCategory()
    {
        return $this->belongsTo(Category::class, 'target_category_id');
    }

    public function rows()
    {
        return $this->hasMany(ContactImportRow::class);
    }
}
