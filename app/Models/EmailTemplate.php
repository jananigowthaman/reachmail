<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'subject',
        'body_html',
        'body_text',
        'default_attachment_id',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    public function defaultAttachment()
    {
        return $this->belongsTo(Attachment::class, 'default_attachment_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
