<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Reset the category badge count cache on create or update.
     */
    public function saved(Category $category): void
    {
        Cache::forget('categories-badge-count');
    }

    /**
     * Reset the category badge count cache on deletion.
     */
    public function deleted(Category $category): void
    {
        Cache::forget('categories-badge-count');
    }
}
