<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Observers\CategoryObserver;
use App\Observers\DepartmentObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use App\Observers\VendorObserver;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);

        Product::observe(ProductObserver::class);
        Vendor::observe(VendorObserver::class);
        Order::observe(OrderObserver::class);
        Category::observe(CategoryObserver::class);
        Department::observe(DepartmentObserver::class);
        User::observe(UserObserver::class);
    }
}
