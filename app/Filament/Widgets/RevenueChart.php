<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Per Month';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = Auth::user();
        $cacheKey = 'widget:revenue_chart:user:'.$user->id;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($user) {
            $query = Order::query();
            $column = $user->hasRole(RolesEnum::Admin) ? 'website_commission' : 'vendor_subtotal';

            if ($user->hasRole(RolesEnum::Vendor)) {
                $query->where('vendor_user_id', $user->id);
            }

            $data = $query
                ->selectRaw("MONTH(created_at) as month, SUM($column) as revenue")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('revenue');

            return [
                'datasets' => [['label' => 'Revenue', 'data' => $data]],
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
