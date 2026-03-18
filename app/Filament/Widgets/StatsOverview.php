<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole(RolesEnum::Admin);
        $cacheKey = 'widget:stats:user:'.$user->id;

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user, $isAdmin) {
            if ($isAdmin) {
                return [
                    $this->countStat('Total Orders this year', Order::query(), 'heroicon-o-shopping-bag'),
                    $this->countStat('Total Products this year', Product::query(), 'heroicon-o-cube'),
                    $this->countStat('Total Vendors this year', User::role(RolesEnum::Vendor), 'heroicon-o-building-storefront'),
                    $this->countStat('Total Users this year', User::role(RolesEnum::User), 'heroicon-o-users'),
                    $this->sumStat('Total Revenue this year', Order::query(), 'total_price', 'heroicon-o-currency-dollar'),
                    $this->sumStat('Website Commission this year', Order::query(), 'website_commission', 'heroicon-o-banknotes'),
                ];
            }

            return [
                $this->countStat('My Orders this year', Order::where('vendor_user_id', $user->id), 'heroicon-o-shopping-bag'),
                $this->countStat('My Products this year', Product::where('created_by', $user->id), 'heroicon-o-cube'),
                $this->sumStat('My Revenue this year', Order::where('vendor_user_id', $user->id), 'vendor_subtotal', 'heroicon-o-currency-dollar'),
            ];
        });
    }

    // ── Stat builders ──────────────────────────────────────────────────────────

    private function countStat(string $label, Builder $query, string $icon): Stat
    {
        [$total, $cur, $prev] = $this->compute($query);

        return Stat::make($label, $total)
            ->description($this->description($cur, $prev))
            ->descriptionIcon($this->icon($cur, $prev))
            ->color($this->color($cur, $prev))
            ->icon($icon);
    }

    private function sumStat(string $label, Builder $query, string $column, string $icon): Stat
    {
        [$total, $cur, $prev] = $this->compute($query, $column);

        return Stat::make($label, '$'.number_format($total, 2))
            ->description($this->description($cur, $prev))
            ->descriptionIcon($this->icon($cur, $prev))
            ->color($this->color($cur, $prev))
            ->icon($icon);
    }

    // ── Core ───────────────────────────────────────────────────────────────────

    private function compute(Builder $query, ?string $column = null): array
    {
        $now = Carbon::now();
        $yearStart = $now->copy()->startOfYear();
        $thisStart = $now->copy()->startOfMonth();
        $prevStart = $now->copy()->subMonth()->startOfMonth();

        $agg = $column ? "SUM($column)" : 'COUNT(*)';

        $total = $column
            ? (clone $query)->whereBetween('created_at', [$yearStart, $now])->sum($column)
            : (clone $query)->whereBetween('created_at', [$yearStart, $now])->count();

        $months = (clone $query)
            ->selectRaw("CASE WHEN created_at >= ? THEN 'cur' ELSE 'prev' END as period, $agg as val", [$thisStart->toDateTimeString()])
            ->whereBetween('created_at', [$prevStart, $now])
            ->groupByRaw('period')
            ->pluck('val', 'period');

        return [$total, (float) ($months['cur'] ?? 0), (float) ($months['prev'] ?? 0)];
    }

    // ── Presentation ───────────────────────────────────────────────────────────

    private function description(float $cur, float $prev): string
    {
        if ($cur == 0 && $prev == 0) {
            return 'No activity this month';
        }
        if ($prev == 0) {
            return '100% increase vs last month';
        }

        $pct = round((($cur - $prev) / $prev) * 100, 1);
        if ($pct > 0) {
            return "{$pct}% increase vs last month";
        }
        if ($pct < 0) {
            return abs($pct).'% decrease vs last month';
        }

        return 'Same as last month';
    }

    private function icon(float $cur, float $prev): string
    {
        if ($cur > $prev) {
            return 'heroicon-o-arrow-trending-up';
        }
        if ($cur < $prev) {
            return 'heroicon-o-arrow-trending-down';
        }

        return 'heroicon-o-minus';
    }

    private function color(float $cur, float $prev): string
    {
        if ($cur > $prev) {
            return 'success';
        }
        if ($cur < $prev) {
            return 'danger';
        }
        if ($cur <= 0) {
            return 'warning';
        }

        return 'gray';
    }
}
