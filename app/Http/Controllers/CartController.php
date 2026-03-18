<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\VendorStatusEnum;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\VendorNotApprovedException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CartController extends Controller
{
    /**
     * Show the cart page with items grouped by vendor.
     */
    public function index(CartService $cartService)
    {
        $groupedItems = $cartService->getCartItemsGrouped();
        $totalQuantity = $cartService->getTotalQuantity();
        $totalPrice = $cartService->getTotalPrice();

        return view('cart.index', [
            'groupedItems' => $groupedItems,
            'totalQuantity' => $totalQuantity,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * Return current cart items, total quantity, and total price as JSON.
     */
    public function items(CartService $cartService)
    {
        return response()->json([
            'items' => $cartService->getCartItems(),
            'totalQuantity' => $cartService->getTotalQuantity(),
            'totalPrice' => $cartService->getTotalPrice(),
        ]);
    }

    /**
     * Add a product to the cart after validating stock and vendor approval.
     */
    public function store(Request $request, Product $product, CartService $cartService)
    {
        $data = $request->validate([
            'option_ids' => ['nullable', 'array'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Check if the product's vendor is approved
        $vendor = $product->user?->vendor;
        if ($vendor) {
            $statusValue = $vendor->status instanceof VendorStatusEnum
                ? $vendor->status->value
                : $vendor->status;

            if ($statusValue !== VendorStatusEnum::Approved->value) {
                throw new VendorNotApprovedException('This vendor is not currently approved to sell.');
            }
        }

        $cartService->addItemToCart(
            $product,
            $data['quantity'],
            $data['option_ids'] ?? []
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to Cart Successfully!',
                'totalQuantity' => $cartService->getTotalQuantity(),
                'totalPrice' => $cartService->getTotalPrice(),
            ]);
        }

        return redirect()->back()->with('success', 'Product added to Cart Successfully!');
    }

    /**
     * Update the quantity of an existing cart item.
     */
    public function update(Request $request, Product $product, CartService $cartService)
    {
        $data = $request->validate([
            'quantity' => ['integer', 'min:1'],
        ]);

        $optionIds = $request->input('option_ids');
        $quantity = $data['quantity'];

        $cartService->updateItemQuantity($product->id, $quantity, $optionIds);

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully',
                'totalQuantity' => $cartService->getTotalQuantity(),
                'totalPrice' => $cartService->getTotalPrice(),
            ]);
        }

        return redirect()->back()->with('success', 'Quantity was updated');
    }

    /**
     * Remove a product from the cart.
     */
    public function destroy(Request $request, Product $product, CartService $cartService)
    {
        $optionIds = $request->input('option_ids');

        $cartService->removeItemFromCart($product->id, $optionIds);

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart',
                'totalQuantity' => $cartService->getTotalQuantity(),
                'totalPrice' => $cartService->getTotalPrice(),
            ]);
        }

        return redirect()->back()->with('success', 'Product was removed from cart');
    }

    /**
     * Build Stripe checkout session for the selected vendor's items or the entire cart,
     * create draft orders per vendor, and redirect the user to Stripe.
     */
    public function checkout(Request $request, CartService $cartService)
    {

        Stripe::setApiKey(config('services.stripe.secret'));

        $vendorId = $request->input('vendor_id');

        $allCartItems = $cartService->getCartItemsGrouped();

        DB::beginTransaction();
        try {
            if ($vendorId) {
                $allCartItems = [$allCartItems[$vendorId]];
            }
            $orders = [];
            $lineItems = [];
            foreach ($allCartItems as $item) {
                $vendorUser = $item['user'];
                $cartItems = $item['items'];

                $order = Order::create([
                    'stripe_session_id' => null,
                    'user_id' => $request->user()->id,
                    'vendor_user_id' => $vendorUser['id'],
                    'total_price' => $item['total_price'],
                    'status' => OrderStatusEnum::Draft->value,
                ]);
                $orders[] = $order;

                foreach ($cartItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem['product_id'],
                        'quantity' => $cartItem['quantity'],
                        'price' => $cartItem['price'],
                        'variation_type_option_ids' => $cartItem['option_ids'],
                    ]);

                    $description = collect($cartItem['options'])->map(function ($item) {
                        return "{$item['type']['name']}: {$item['name']}";
                    })->implode(', ');

                    $lineItem = [
                        'price_data' => [
                            'currency' => config('services.stripe.currency'),
                            'product_data' => [
                                'name' => $cartItem['title'],
                                'images' => $cartItem['image'] ? [$cartItem['image']] : [],
                            ],
                            'unit_amount' => (int) round($cartItem['price'] * 100),
                        ],
                        'quantity' => $cartItem['quantity'],
                    ];
                    if ($description) {
                        $lineItem['price_data']['product_data']['description'] = $description;
                    }
                    $lineItems[] = $lineItem;
                }
            }

            $session = Session::create([
                'customer_email' => $request->user()->email,
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('stripe.success', []).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel', []),
            ]);

            foreach ($orders as $order) {
                $order->stripe_session_id = $session->id;
                $order->save();
            }

            DB::commit();

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();

            $message = $e->getMessage() ?: 'Payment processing failed. Please try again.';
            throw new PaymentFailedException($message);
        }
    }
}
