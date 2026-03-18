<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Show the homepage with a paginated, cached list of published products.
     */
    public function index(Request $request)
    {
        $keyword = $request->query('keyword');
        $page = $request->input('page', 1);
        $version = Cache::get('products:version', 1);

        $cacheKey = 'products:v'.$version.':'.md5(json_encode($keyword).$page);

        $products = Cache::remember($cacheKey, 3600, function () use ($keyword) {
            return Product::query()
                ->with(['variationTypes.options.media', 'department'])
                ->forWebsite()
                ->filter($keyword)
                ->paginate(12);
        });

        return view('home', ['products' => $products]);
    }

    /**
     * Display a single product with its variations, images, and vendor info.
     */
    public function show(Product $product)
    {
        $cacheKey = "products:show:{$product->slug}";

        $productData = Cache::remember($cacheKey, 3600, function () use ($product) {
            $product->load([
                'variationTypes.options.media',
                'variations',
                'media',
                'user.vendor',
                'department',
                'category',
            ]);

            return [
                'product' => $product,
                'variationTypes' => $product->getVariationTypesData(),
                'productVariations' => $product->variations->map(fn ($v) => [
                    'id' => $v->id,
                    'variation_type_option_ids' => $v->variation_type_option_ids,
                    'price' => $v->price,
                    'quantity' => $v->quantity,
                ])->toArray(),
                'productImages' => $product->getProductImagesData(),
            ];
        });

        return view('products.show', $productData);
    }

    /**
     * List products within a specific department, with optional category and keyword filters.
     */
    public function byDepartment(Request $request, Department $department)
    {
        abort_unless($department->active, 404);

        $keyword = $request->query('keyword');
        $categoryId = $request->integer('category_id') ?? null;
        $page = $request->query('page', 1);
        $version = Cache::get('products:version', 1);

        $categories = $department->categories()->orderBy('name')->get();
        $activeCategory = $categoryId ? $categories->firstWhere('id', $categoryId) : null;

        $cacheKey = sprintf(
            'products:v%d:dept:%d:cat:%s:page:%d:keyword:%s',
            $version,
            $department->id,
            $categoryId ?: 'all',
            $page,
            $keyword ?: 'all'
        );

        $products = Cache::remember($cacheKey, 3600, function () use ($keyword, $department, $activeCategory) {
            return Product::query()
                ->forWebsite()
                ->where('department_id', $department->id)
                ->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id))
                ->filter($keyword)
                ->paginate(12);
        });

        return view('department.index', compact('department', 'categories', 'activeCategory', 'products', 'keyword'));
    }
}
