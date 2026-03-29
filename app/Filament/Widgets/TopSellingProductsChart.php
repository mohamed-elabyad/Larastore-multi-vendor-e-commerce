<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class TopSellingProductsChart extends ChartWidget
{
    protected ?string $heading = 'Top Selling Products';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $query = OrderItem::query()
            ->join('products', 'products.id', '=', 'order_items.product_id');

        if ($user->hasRole(RolesEnum::Vendor)) {
            $query->where('products.created_by', $user->id);
        }

        $data = $query->selectRaw('products.title, SUM(order_items.quantity) as total')
            ->groupBy('products.title')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'title');

        $shortened = $data->mapWithKeys(
            fn($value, $key) => [
                (strlen($key) > 20 ? substr($key, 0, 20) . '...' : $key) => $value
            ]
        );

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data'  => $shortened->values(),
                    'backgroundColor' => [
                        '#6366f1',
                        '#f59e0b',
                        '#10b981',
                        '#ef4444',
                        '#3b82f6',
                    ],
                ],
            ],
            'labels' => $shortened->keys()
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
