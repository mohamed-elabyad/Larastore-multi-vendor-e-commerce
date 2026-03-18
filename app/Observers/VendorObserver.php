<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;

class VendorObserver
{
    public function saved(Vendor $vendor): void
    {
        $this->clearVendorCache($vendor);
    }

    public function deleted(Vendor $vendor): void
    {
        $this->clearVendorCache($vendor);
    }

    private function clearVendorCache(Vendor $vendor): void
    {
        // Frontend vendor profile cache
        $version = Cache::get("vendor:{$vendor->id}:version", 1);
        Cache::put("vendor:{$vendor->id}:version", $version + 1, now()->addDays(30));

        // Sidebar badges
        Cache::forget('vendors-badge-count');
        Cache::forget('users-badge-count');

        // StatsOverview يعرض عداد الـ vendors — لازم يتمسح عند إضافة/حذف vendor
        User::role('admin')->pluck('id')->each(function ($adminId) {
            Cache::forget("widget:stats:user:{$adminId}");
        });
    }
}
