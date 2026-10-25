<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'category_contact');
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_categories');
    }
}
