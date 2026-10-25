<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactImportRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_import_id',
        'row_number',
        'raw_data',
        'status',
        'reason',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    public function import()
    {
        return $this->belongsTo(ContactImport::class, 'contact_import_id');
    }
}
