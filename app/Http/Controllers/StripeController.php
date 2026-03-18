<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Mail\CheckoutCompletedMail;
use App\Mail\NewOrderMail;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Show the success page after a completed Stripe checkout.
     */
    public function success(Request $request)
    {
        $user = Auth::user();
        $session_id = $request->session_id;

        $orders = Order::where('user_id', $user->id)
            ->where('stripe_session_id', $session_id)
            ->get();

        abort_if($orders->isEmpty(), 404);

        return view('stripe.success', ['orders' => $orders]);
    }

    /**
     * Show the cancellation page when the user abandons checkout.
     */
    public function cancel()
    {
        return view('stripe.cancel');
    }

    /**
     * Check whether all orders tied to a session have been paid (used for polling).
     */
    public function orderStatus(Request $request)
    {
        $user = Auth::user();
        $session_id = $request->query('session_id');

        if (! $session_id || ! $user) {
            return response()->json(['status' => 'unknown']);
        }

        $orders = Order::where('user_id', $user->id)
            ->where('stripe_session_id', $session_id)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['status' => 'unknown']);
        }

        // All orders paid = confirmed
        $allPaid = $orders->every(fn ($o) => $o->status === OrderStatusEnum::Paid || $o->status === OrderStatusEnum::Paid->value);

        return response()->json([
            'status' => $allPaid ? 'paid' : 'pending',
        ]);
    }

    /**
     * Handle incoming Stripe webhook events (checkout completion, charge updates).
     */
    public function webhook(Request $request)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        $webhook_secret = config('services.stripe.webhook_secret');

        $payload = $request->getContent();

        $sig_header = request()->header('Stripe-Signature');

        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $webhook_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error($e);

            return response('Invalid Payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::error($e);

            return response('Invalid Payload', 400);
        }

        switch ($event->type) {
            case 'charge.updated':

                $charge = $event->data->object;
                $transactionId = $charge['balance_transaction'];
                $paymentIntent = $charge['payment_intent'];
                $balanceTransaction = $stripe->balanceTransactions->retrieve($transactionId);

                $orders = Order::with(['user', 'vendorUser', 'orderItems.product', 'vendor'])
                    ->where('payment_intent', $paymentIntent)
                    ->get();

                $totalAmount = $balanceTransaction['amount'];
                $stripeFee = 0;

                foreach ($balanceTransaction['fee_details'] as $fee_detail) {
                    if ($fee_detail['type'] === 'stripe_fee') {
                        $stripeFee = $fee_detail['amount'];
                    }
                }

                $platformFeePercent = config('services.stripe.platform_fee_pct');

                foreach ($orders as $order) {
                    // Stripe amounts are in cents, total_price is in dollars
                    // vendorShare = ratio of this order's price to total charged amount
                    $vendorShare = $order->total_price / ($totalAmount / 100);

                    /** @var Order $order */
                    // online_payment_commission = vendor's proportional share of Stripe fee (in dollars)
                    $order->online_payment_commission = round(($vendorShare * ($stripeFee / 100)), 2);

                    $order->website_commission = round(
                        ($order->total_price - $order->online_payment_commission) / 100 * $platformFeePercent,
                        2
                    );

                    $order->vendor_subtotal = round(
                        $order->total_price - $order->online_payment_commission - $order->website_commission,
                        2
                    );

                    $order->save();

                    Mail::to($order->vendorUser)
                        ->send(new NewOrderMail($order));
                }

                Mail::to($orders[0]->user)
                    ->send(new CheckoutCompletedMail($orders));

                break;

            case 'checkout.session.completed':

                $session = $event->data->object;
                $pi = $session['payment_intent'];

                // find orders by session id and set payment intent
                $orders = Order::query()
                    ->with(['orderItems'])
                    ->where(['stripe_session_id' => $session['id']])
                    ->get();

                $productsToDeleteFromCart = [];
                $userId = null;

                foreach ($orders as $order) {
                    $order->payment_intent = $pi;
                    $order->status = OrderStatusEnum::Paid;
                    $order->save();

                    $userId = $order->user_id;

                    $productsToDeleteFromCart = [
                        ...$productsToDeleteFromCart,
                        ...$order->orderItems->map(fn ($item) => $item->product_id)->toArray(),
                    ];

                    // reduce product quantity
                    foreach ($order->orderItems as $orderItem) {
                        /** @var OrderItem $orderItem */
                        $options = $orderItem->variation_type_option_ids;
                        $product = $orderItem->product;

                        if ($options) {
                            sort($options);
                            $variation = $product->variations()
                                ->where('variation_type_option_ids', $options)
                                ->first();

                            if ($variation && $variation->quantity != null) {
                                $variation->quantity -= $orderItem->quantity;
                                $variation->save();
                            }
                        } elseif ($product->quantity != null) {
                            $product->quantity -= $orderItem->quantity;
                            $product->save();
                        }
                    }
                }

                if ($userId && ! empty($productsToDeleteFromCart)) {
                    Cart::query()
                        ->where('user_id', $userId)
                        ->whereIn('product_id', $productsToDeleteFromCart)
                        ->where('saved_for_later', false)
                        ->delete();
                }
                break;

            default:
                echo 'Received unknown event type'.$event->type;
        }

        return response('', 200);
    }
}
