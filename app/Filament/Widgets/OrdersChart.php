<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Orders Per Month';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $user = Auth::user();
        $cacheKey = 'widget:orders_chart:user:'.$user->id;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($user) {
            $query = Order::query();
            if ($user->hasRole(RolesEnum::Vendor)) {
                $query->where('vendor_user_id', $user->id);
            }

            $data = $query
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total');

            return [
                'datasets' => [['label' => 'Orders', 'data' => $data]],
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
