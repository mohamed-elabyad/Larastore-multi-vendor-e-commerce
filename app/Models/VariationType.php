<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariationType extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * Get the option values available for this variation type.
     */
    public function options(): HasMany
    {
        return $this->hasMany(VariationTypeOption::class);
    }

    /**
     * Get the product this variation type belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
