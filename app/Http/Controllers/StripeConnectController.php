<?php

namespace App\Http\Controllers;

use App\Services\StripeConnectService;
use Illuminate\Support\Facades\Auth;
use Stripe\Account;
use Stripe\Stripe;

class StripeConnectController extends Controller
{
    public function __construct(public StripeConnectService $stripeConnect) {}

    /**
     * Create a Stripe Express account for the vendor (if needed) and redirect to onboarding.
     */
    public function connect()
    {
        $user = Auth::user();

        if (! $user->stripe_account_id) {
            $this->stripeConnect->createStripeAccount($user, ['type' => 'express']);
        }

        if (! $user->stripe_account_active) {
            return redirect($this->stripeConnect->getStripeAccountLink($user));
        }

        return redirect()->back()->with('success', 'Your account is already connected');
    }

    /**
     * Handle the redirect back from Stripe after the vendor completes onboarding.
     */
    public function handleReturn()
    {
        /** @var User $user */
        $user = Auth::user();

        Stripe::setApiKey(config('services.stripe.secret'));

        $account = Account::retrieve($user->stripe_account_id);

        $user->update([
            'stripe_account_active' => $account->charges_enabled,
        ]);

        return redirect()->route('profile.edit');
    }

    /**
     * Redirect the vendor back to their profile when Stripe link expires.
     */
    public function refresh()
    {
        return redirect()->route('profile.edit');
    }
}
