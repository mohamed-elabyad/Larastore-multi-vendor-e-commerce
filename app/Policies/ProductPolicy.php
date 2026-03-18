<?php

namespace App\Policies;

use App\Enums\RolesEnum;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // كل المستخدمين المسجلين يقدروا يشوفوا قائمة المنتجات في Filament
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasRole(RolesEnum::Admin)
            || $product->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RolesEnum::Admin)
            || $user->hasRole(RolesEnum::Vendor);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasRole(RolesEnum::Admin)
            || $product->created_by === $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasRole(RolesEnum::Admin)
            || $product->created_by === $user->id;
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->hasRole(RolesEnum::Admin);
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasRole(RolesEnum::Admin);
    }
}
