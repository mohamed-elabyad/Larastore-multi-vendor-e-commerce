<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductsAddedChart extends ChartWidget
{
    protected ?string $heading = 'Products Added Per Month';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $user = Auth::user();
        $cacheKey = 'widget:products_chart:user:'.$user->id;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($user) {
            $query = Product::query();
            if ($user->hasRole(RolesEnum::Vendor)) {
                $query->where('created_by', $user->id);
            }

            $data = $query
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total');

            return [
                'datasets' => [['label' => 'Products', 'data' => $data]],
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
