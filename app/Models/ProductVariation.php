<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'variation_type_option_ids' => 'json',
    ];

    /**
     * Get the URL of the first image attached to this variation.
     */
    public function getImageAttribute()
    {
        return $this->getFirstMediaUrl('images');
    }
}
