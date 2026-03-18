<?php

namespace App\Services;

use App\Exceptions\CartQuantityExceededException;
use App\Exceptions\ProductOutOfStockException;
use App\Models\Cart;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartService
{
    private ?array $cachedCartItems = null;

    protected const COOKIE_NAME = 'cartItems';

    protected const COOKIE_LIFETIME = 60 * 24 * 365; // 1 year

    /**
     * Convert option IDs to a sorted array of integers.
     *
     * @return int[]
     */
    private function normalizeOptionIds(mixed $optionIds): array
    {
        if (is_string($optionIds)) {
            $optionIds = json_decode($optionIds, true) ?? [];
        }

        $optionIds = array_values((array) ($optionIds ?? []));
        $optionIds = array_map('intval', $optionIds);
        sort($optionIds);

        return $optionIds;
    }

    /**
     * Add a product to the cart with stock validation.
     */
    public function addItemToCart(Product $product, int $quantity = 1, $optionIds = [])
    {
        // If no options provided and product has variations, get first option
        if (empty($optionIds) && $product->variationTypes->count() > 0) {
            $optionIds = $product->variationTypes
                ->map(fn (VariationType $type) => $type->options[0]?->id)
                ->filter()
                ->toArray();
        }

        $optionIds = $this->normalizeOptionIds($optionIds);

        // Stock check
        if (! empty($optionIds)) {
            $variation = $product->variations()
                ->get()
                ->first(function ($v) use ($optionIds) {
                    $vIds = $v->variation_type_option_ids ?? [];
                    sort($vIds);

                    return $vIds == $optionIds;
                });

            if ($variation && $variation->quantity !== null) {
                if ($variation->quantity === 0) {
                    throw new ProductOutOfStockException('This variation is out of stock.');
                }
                if ($quantity > $variation->quantity) {
                    throw new CartQuantityExceededException(
                        "Only {$variation->quantity} unit(s) available for this variation."
                    );
                }
            }
        } else {
            if ($product->quantity !== null && $product->quantity === 0) {
                throw new ProductOutOfStockException('This product is out of stock.');
            }
            if ($product->quantity !== null && $quantity > $product->quantity) {
                throw new CartQuantityExceededException(
                    "Only {$product->quantity} unit(s) available for this product."
                );
            }
        }

        $price = $product->getPriceForOptions($optionIds);

        if (Auth::check()) {
            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }

    /**
     * Update the quantity of an existing cart item.
     */
    public function updateItemQuantity(int $productId, int $quantity, $optionIds = null)
    {
        $optionIds = $this->normalizeOptionIds($optionIds);

        if (Auth::check()) {
            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else {
            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItemFromCart(int $productId, $optionIds = null)
    {
        $optionIds = $this->normalizeOptionIds($optionIds);

        if (Auth::check()) {
            $this->removeItemFromDatabase($productId, $optionIds);
        } else {
            $this->removeItemFromCookies($productId, $optionIds);
        }
    }

    /**
     * Get all cart items with product details, options, and images.
     */
    public function getCartItems()
    {
        try {
            if ($this->cachedCartItems === null) {
                if (Auth::check()) {
                    $cartItems = $this->getCartItemsFromDatabase();
                } else {
                    $cartItems = $this->getCartItemsFromCookies();
                }

                $productIds = collect($cartItems)->map(fn ($item) => $item['product_id']);

                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');

                $cartItemData = [];

                foreach ($cartItems as $key => $cartItem) {
                    $product = data_get($products, $cartItem['product_id']);
                    if (! $product) {
                        continue;
                    }

                    $optionInfo = [];
                    $imageUrl = null;
                    $variationUrlParams = [];
                    $optionIds = $this->normalizeOptionIds($cartItem['option_ids'] ?? []);

                    if (! empty($optionIds)) {
                        $options = VariationTypeOption::with('variationType')
                            ->whereIn('id', $optionIds)
                            ->get()
                            ->keyBy('id');

                        foreach ($optionIds as $option_id) {
                            $option = data_get($options, $option_id);
                            if (! $option) {
                                continue;
                            }

                            if (! $imageUrl) {
                                $imageUrl = $option->getFirstMediaUrl('images', 'thumb');
                            }

                            $optionInfo[] = [
                                'id' => $option->id,
                                'name' => $option->name,
                                'type' => [
                                    'id' => $option->variationType->id,
                                    'name' => $option->variationType->name,
                                ],
                            ];

                            $variationUrlParams['options['.$option->variationType->id.']'] = $option->id;
                        }
                    }

                    $variationUrlParams['quantity'] = $cartItem['quantity'];

                    $productUrl = route('products.show', $product->slug);
                    if (! empty($variationUrlParams)) {
                        $productUrl .= '?'.http_build_query($variationUrlParams);
                    }

                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $cartItem['price'],
                        'quantity' => $cartItem['quantity'],
                        'option_ids' => $optionIds,
                        'options' => $optionInfo,
                        'image' => $imageUrl ?: $product->getFirstMediaUrl('images', 'thumb'),
                        'variation_url' => $productUrl,
                        'user' => [
                            'id' => $product->created_by,
                            'name' => $product->user->vendor->store_name,
                        ],
                    ];
                }

                $this->cachedCartItems = $cartItemData;
            }

            return $this->cachedCartItems;
        } catch (\Exception $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }

        return [];
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getTotalQuantity()
    {
        $totalQuantity = 0;
        foreach ($this->getCartItems() as $item) {
            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }

    /**
     * Get the total price of all cart items.
     */
    public function getTotalPrice()
    {
        $total = 0;

        foreach ($this->getCartItems() as $item) {
            $total += $item['quantity'] * $item['price'];
        }

        return $total;
    }

    /**
     * Group cart items by seller with totals for quantity and price.
     */
    public function getCartItemsGrouped()
    {
        $cartItems = $this->getCartItems();

        return collect($cartItems)
            ->groupBy(fn ($item) => $item['user']['id'])
            ->map(fn ($items, $userId) => [
                'user' => $items->first()['user'],
                'items' => $items->toArray(),
                'total_quantity' => $items->sum('quantity'),
                'total_price' => $items->sum(fn ($item) => $item['price'] * $item['quantity']),
            ])
            ->toArray();
    }

    /**
     * Transfer cart items from cookies to the database on login.
     */
    public function moveCartItemsToDatabase($userId)
    {
        $cartItems = $this->getCartItemsFromCookies();

        foreach ($cartItems as $itemKey => $cartItem) {
            $optionIds = $this->normalizeOptionIds($cartItem['option_ids'] ?? []);

            $existingItem = Cart::where('user_id', $userId)
                ->where('product_id', $cartItem['product_id'])
                ->where('variation_type_option_ids', json_encode($optionIds))
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $cartItem['quantity'],
                    'price' => $cartItem['price'],
                ]);
            } else {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $cartItem['product_id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price'],
                    'variation_type_option_ids' => $optionIds,
                ]);
            }
        }

        Cookie::queue(self::COOKIE_NAME, '', -1); // delete cookie
    }

    /**
     * Save or increment a cart item in the database.
     */
    protected function saveItemToDatabase(int $productId, int $quantity, $price, array $optionIds)
    {
        $userId = Auth::id();

        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => DB::raw('quantity + '.$quantity),
            ]);
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds,
            ]);
        }
    }

    /**
     * Set a new quantity for a cart item in the database.
     */
    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds)
    {
        $userId = Auth::id();

        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->first();

        if ($cartItem) {
            $cartItem->update(['quantity' => $quantity]);
            $this->cachedCartItems = null;
        }
    }

    /**
     * Delete a cart item from the database.
     */
    protected function removeItemFromDatabase(int $productId, array $optionIds)
    {
        $userId = Auth::id();

        Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variation_type_option_ids', json_encode($optionIds))
            ->delete();

        $this->cachedCartItems = null;
    }

    //  Cookie internals – $optionIds is ALWAYS a sorted int[]

    /**
     * Save or increment a cart item in cookies.
     */
    protected function saveItemToCookies(int $productId, int $quantity, $price, array $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();

        $itemKey = $productId.'_'.json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] += $quantity;
        } else {
            $cartItems[$itemKey] = [
                'id' => Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds,
            ];
        }

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    /**
     * Set a new quantity for a cart item in cookies.
     */
    protected function updateItemQuantityInCookies(int $productId, int $quantity, array $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();

        $itemKey = $productId.'_'.json_encode($optionIds);

        if (isset($cartItems[$itemKey])) {
            $cartItems[$itemKey]['quantity'] = $quantity;
        }

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    /**
     * Delete a cart item from cookies.
     */
    protected function removeItemFromCookies(int $productId, array $optionIds)
    {
        $cartItems = $this->getCartItemsFromCookies();

        $cartKey = $productId.'_'.json_encode($optionIds);

        unset($cartItems[$cartKey]);

        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    //  Data sources /////////////////

    /**
     * Fetch raw cart items from the database for the current user.
     */
    protected function getCartItemsFromDatabase()
    {
        $userId = Auth::id();

        return Cart::where('user_id', $userId)
            ->get()
            ->map(fn ($cartItem) => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price,
                'option_ids' => $cartItem->variation_type_option_ids,
            ])
            ->toArray();
    }

    /**
     * Fetch raw cart items from the cookie.
     */
    protected function getCartItemsFromCookies()
    {
        $cartItems = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);

        return array_map(function ($item) {
            if (isset($item['variation_type_option_ids'])) {
                $item['option_ids'] = is_string($item['variation_type_option_ids'])
                    ? json_decode($item['variation_type_option_ids'], true)
                    : $item['variation_type_option_ids'];
            }

            return $item;
        }, $cartItems ?: []);
    }
}
