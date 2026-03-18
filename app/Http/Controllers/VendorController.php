<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Show a vendor's public storefront with their products, departments, and categories.
     */
    public function profile(Request $request, Vendor $vendor)
    {
        $keyword = $request->query('keyword');
        $departmentId = $request->integer('department_id') ?: null;
        $categoryId = $request->integer('category_id') ?: null;

        // Departments this vendor actually sells in
        $departments = Department::query()
            ->whereIn('id',
                Product::where('created_by', $vendor->user_id)
                    ->where('status', ProductStatusEnum::Published)
                    ->pluck('department_id')
                    ->unique()
            )
            ->orderBy('name')
            ->get();

        $activeDepartment = $departmentId
            ? $departments->firstWhere('id', $departmentId)
            : null;

        // Categories within the chosen department that this vendor sells in
        $categories = collect();
        $activeCategory = null;

        if ($activeDepartment) {
            $categories = Category::query()
                ->whereIn('id',
                    Product::where('created_by', $vendor->user_id)
                        ->where('status', ProductStatusEnum::Published)
                        ->where('department_id', $activeDepartment->id)
                        ->pluck('category_id')
                        ->unique()
                )
                ->orderBy('name')
                ->get();

            $activeCategory = $categoryId
                ? $categories->firstWhere('id', $categoryId)
                : null;
        }

        // Products query — only this vendor's published products
        $products = Product::query()
            ->where('products.created_by', $vendor->user_id)
            ->where('products.status', ProductStatusEnum::Published)
            ->when($activeDepartment, fn ($q) => $q->where('products.department_id', $activeDepartment->id))
            ->when($activeCategory, fn ($q) => $q->where('products.category_id', $activeCategory->id))
            ->when($keyword, fn ($q) => $q->where(function ($q) use ($keyword) {
                $q->where('products.title', 'LIKE', "%{$keyword}%")
                    ->orWhere('products.description', 'LIKE', "%{$keyword}%");
            }))
            ->paginate(12);

        return view('Vendors.profile', compact(
            'vendor', 'keyword',
            'departments', 'activeDepartment',
            'categories', 'activeCategory',
            'products'
        ));
    }
}
