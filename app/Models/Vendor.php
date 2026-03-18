<?php

namespace App\Models;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'store_name',
        'store_address',
        'cover_image',
        'status',
    ];

    /**
     * Scope to vendors who are approved and have an active Stripe account.
     */
    public function scopeEligibleForPayout(Builder $query): Builder
    {
        return $query->where('status', VendorStatusEnum::Approved)
            ->join('users', 'users.id', '=', 'vendors.user_id')
            ->where('users.stripe_account_active', true);
    }

    /**
     * Get the user account that owns this vendor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
