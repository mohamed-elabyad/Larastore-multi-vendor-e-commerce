<?php

namespace App\Services;

use App\Models\User;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;
use Stripe\Transfer;

class StripeConnectService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a new Stripe Express account and store its ID on the user.
     */
    public function createStripeAccount(User $user, array $options = []): void
    {
        $account = Account::create($options);

        $user->update(['stripe_account_id' => $account->id]);
    }

    /**
     * Generate a Stripe onboarding link for the given user's connected account.
     */
    public function getStripeAccountLink(User $user): string
    {
        $link = AccountLink::create([
            'account' => $user->stripe_account_id,
            'refresh_url' => route('stripe.refresh'),
            'return_url' => route('stripe.return'),
            'type' => 'account_onboarding',
        ]);

        return $link->url;
    }

    /**
     * Transfer funds to the vendor's connected Stripe account.
     */
    public function transfer(User $user, int $amount, string $currency = 'usd'): Transfer
    {
        return Transfer::create([
            'amount' => $amount,
            'currency' => $currency,
            'destination' => $user->stripe_account_id,
        ]);
    }
}
