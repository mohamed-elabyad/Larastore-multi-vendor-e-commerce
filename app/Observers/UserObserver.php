<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    public function saved(User $user): void
    {
        $this->clearUserCache();
    }

    public function deleted(User $user): void
    {
        $this->clearUserCache();
    }

    private function clearUserCache(): void
    {
        // Sidebar badge
        Cache::forget('users-badge-count');

        // StatsOverview يعرض "Total Users" و "Total Vendors" — لازم يتمسح
        User::role('admin')->pluck('id')->each(function ($adminId) {
            Cache::forget("widget:stats:user:{$adminId}");
        });
    }
}
