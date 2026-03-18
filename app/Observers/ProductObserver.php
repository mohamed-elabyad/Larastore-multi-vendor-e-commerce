<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    /**
     * Clear product caches and bump version whenever a product is created or updated.
     */
    public function saved(Product $product): void
    {
        Cache::forget("products:show:{$product->slug}");
        Cache::forget('products-badge-count');
        if ($product->created_by) {
            Cache::forget("products-badge-count:user:{$product->created_by}");
        }
        $this->bumpProductsVersion();
        $this->clearProductWidgetCache($product);
    }

    /**
     * Clear product caches and bump version when a product is deleted.
     */
    public function deleted(Product $product): void
    {
        Cache::forget("products:show:{$product->slug}");
        Cache::forget('products-badge-count');
        if ($product->created_by) {
            Cache::forget("products-badge-count:user:{$product->created_by}");
        }
        $this->bumpProductsVersion();
        $this->clearProductWidgetCache($product);
    }

    /**
     * Increment the global product listing cache version.
     */
    private function bumpProductsVersion(): void
    {
        $version = Cache::get('products:version', 1);
        Cache::put('products:version', $version + 1, now()->addDays(30));
    }

    /**
     * Flush cached widget data for the product's vendor and all admins.
     */
    private function clearProductWidgetCache(Product $product): void
    {
        if ($product->created_by) {
            Cache::forget("widget:stats:user:{$product->created_by}");
            Cache::forget("widget:products_chart:user:{$product->created_by}");
        }

        User::role('admin')->pluck('id')->each(function ($adminId) {
            Cache::forget("widget:stats:user:{$adminId}");
            Cache::forget("widget:products_chart:user:{$adminId}");
        });
    }
}
