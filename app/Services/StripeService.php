<?php

namespace App\Services\Payment;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession($items, $successUrl, $cancelUrl)
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $this->formatLineItems($items),
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    private function formatLineItems($items)
    {
        return collect($items)->map(function ($item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->product->title,
                    ],
                    'unit_amount' => $item->product->price * 100, // Stripe يستخدم cents
                ],
                'quantity' => $item->quantity,
            ];
        })->toArray();
    }
}
