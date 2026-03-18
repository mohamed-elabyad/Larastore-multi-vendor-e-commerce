<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Register thumbnail, small, and large image conversions for product media.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100);

        $this->addMediaConversion('small')
            ->width(480);

        $this->addMediaConversion('large')
            ->width(1200);
    }

    /**
     * Get the user (vendor) who created this product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the department this product belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the category this product is listed under.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the variation types defined for this product (e.g. Size, Color).
     */
    public function variationTypes(): HasMany
    {
        return $this->hasMany(VariationType::class);
    }

    /**
     * Get all variation combinations with their price and stock.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    /**
     * Scope to only products created by the currently authenticated vendor.
     */
    public function scopeForVendor(Builder $query): Builder
    {
        return $query->where('created_by', Auth::user()->id);
    }

    /**
     * Scope to only published products.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('products.status', ProductStatusEnum::Published);
    }

    /**
     * Scope to products whose vendor has been approved.
     */
    public function scopeVendorApproved(Builder $query)
    {
        return $query->join('vendors', 'vendors.user_id', '=', 'products.created_by')
            ->where('vendors.status', VendorStatusEnum::Approved->value);
    }

    /**
     * Scope combining published status and vendor approval for the public storefront.
     */
    public function scopeForWebsite(Builder $query): Builder
    {
        return $query->published()->vendorApproved();
    }

    /**
     * Scope to filter products by a keyword against title and description.
     */
    public function scopeFilter(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword ?? null, function ($query, $keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        });
    }

    // Image helpers

    /** Format a single Spatie Media object into the standard URL array */
    private function mediaUrls(Media $media): array
    {
        return [
            'thumb' => $media->getUrl('thumb'),
            'small' => $media->getUrl('small'),
            'large' => $media->getUrl('large'),
            'original' => $media->getUrl(),
        ];
    }

    /**
     * Returns the product's own images formatted for Alpine.js.
     * Replaces the repeated `getMedia('images')->map(...)` block in the controller.
     */
    public function getProductImagesData(): array
    {
        return $this->getMedia('images')
            ->map(fn (Media $media) => $this->mediaUrls($media))
            ->toArray();
    }

    /**
     * Returns variation types with their options and images formatted for Alpine.js.
     * Replaces the nested map/map/map block in the controller.
     */
    public function getVariationTypesData(): array
    {
        return $this->variationTypes->map(function ($variationType) {
            return [
                'id' => $variationType->id,
                'name' => $variationType->name,
                'type' => $variationType->type,
                'options' => $variationType->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->name,
                        'images' => $option->getMedia('images')
                            ->map(fn (Media $media) => $this->mediaUrls($media))
                            ->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Get the product's primary image URL, falling back to variation option images.
     */
    public function getImageAttribute()
    {
        $image = $this->getFirstMediaUrl('images');

        if (! $image) {
            $image = $this->variationTypes->flatMap->options->flatMap->getMedia('images')->first()?->getUrl();
        }

        return $image;
    }

    /**
     * Look up the price for a specific set of variation options, falling back to base price.
     */
    public function getPriceForOptions($optionIds = [])
    {
        sort($optionIds);
        foreach ($this->variations as $variation) {
            $a = $variation->variation_type_option_ids;
            sort($a);
            if ($optionIds == $a) {
                return $variation->price;
            }
        }

        return $this->price;
    }

    /**
     * Get the image URL matching the given variation options, or the product's default.
     */
    public function getImageForOptions(array $optionIds = [])
    {
        if ($optionIds) {
            $optionIds = array_values($optionIds);
            sort($optionIds);
            $options = VariationTypeOption::whereIn('id', $optionIds)->get();

            foreach ($options as $option) {
                $image = $option->getFirstMediaUrl('images', 'small');
                if ($image) {
                    return $image;
                }
            }
        }

        return $this->getFirstMediaUrl('images', 'small');
    }
}
