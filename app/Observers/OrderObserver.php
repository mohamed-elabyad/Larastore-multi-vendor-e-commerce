<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class OrderObserver
{
    /**
     * Flush order-related widget caches when an order is created or updated.
     */
    public function saved(Order $order): void
    {
        $this->clearOrderWidgetCache($order);
    }

    /**
     * Flush order-related widget caches when an order is deleted.
     */
    public function deleted(Order $order): void
    {
        $this->clearOrderWidgetCache($order);
    }

    /**
     * Clear cached stats for the order's vendor and all admin users.
     */
    private function clearOrderWidgetCache(Order $order): void
    {
        // Vendor associated with this order
        if ($order->vendor_user_id) {
            Cache::forget("widget:stats:user:{$order->vendor_user_id}");
            Cache::forget("widget:orders_chart:user:{$order->vendor_user_id}");
            Cache::forget("widget:revenue_chart:user:{$order->vendor_user_id}");
            Cache::forget("widget:orders_status:user:{$order->vendor_user_id}");
        }

        // All admin users
        User::role('admin')->pluck('id')->each(function ($adminId) {
            Cache::forget("widget:stats:user:{$adminId}");
            Cache::forget("widget:orders_chart:user:{$adminId}");
            Cache::forget("widget:revenue_chart:user:{$adminId}");
            Cache::forget("widget:orders_status:user:{$adminId}");
        });
    }
}
