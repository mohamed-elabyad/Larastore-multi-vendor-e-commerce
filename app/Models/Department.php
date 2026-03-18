<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the categories under this department.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Scope to only active (published) departments.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Get the products belonging to this department.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }
}
