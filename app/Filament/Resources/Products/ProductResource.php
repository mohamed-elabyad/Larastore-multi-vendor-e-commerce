<?php

namespace App\Filament\Resources\Products;

use App\Enums\RolesEnum;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ProductImages;
use App\Filament\Resources\Products\Pages\ProductVariations;
use App\Filament\Resources\Products\Pages\ProductVariationTypes;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Products Management';

    protected static ?int $navigationSort = 3;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getEloquentQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole(RolesEnum::Admin)) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->forVendor();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole(RolesEnum::Admin);

        $cacheKey = $isAdmin
            ? 'products-badge-count'
            : "products-badge-count:user:{$user->id}";

        return Cache::remember($cacheKey, 1800, function () use ($user, $isAdmin) {
            return $isAdmin
                ? static::getModel()::count()
                : static::getModel()::where('created_by', $user->id)->count();
        });
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'user' => $record->user->name,
            'vendor' => $record->user->vendor->store_name,
            'department' => $record->department?->name,
            'category' => $record->category?->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $query = parent::getGlobalSearchEloquentQuery()
            ->with(['user.vendor', 'department', 'category']);

        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole(RolesEnum::Vendor)) {
            $query->where('created_by', $user->id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
            'images' => ProductImages::route('{record}/images'),
            'variation-types' => ProductVariationTypes::route('/{record}/variation-types'),
            'variations' => ProductVariations::route('/{record}/variations'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditProduct::class,
            ProductImages::class,
            ProductVariationTypes::class,
            ProductVariations::class,
        ]);
    }
}
