<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OrdersStatusChart extends ChartWidget
{
    protected ?string $heading = 'Orders Status';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $user = Auth::user();
        $cacheKey = 'widget:orders_status:user:'.$user->id;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($user) {
            $query = Order::query();
            if ($user->hasRole(RolesEnum::Vendor)) {
                $query->where('vendor_user_id', $user->id);
            }

            $data = $query
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'datasets' => [[
                    'data' => $data->values(),
                    'backgroundColor' => ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6'],
                    'hoverOffset' => 4,
                ]],
                'labels' => $data->keys(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
