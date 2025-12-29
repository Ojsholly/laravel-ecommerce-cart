<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    public function getOrCreateWishlist(User $user): Wishlist
    {
        return Wishlist::firstOrCreate(['user_id' => $user->id]);
    }

    public function addProduct(Wishlist $wishlist, Product $product): WishlistItem
    {
        return WishlistItem::firstOrCreate([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);
    }

    public function removeProduct(WishlistItem $item): void
    {
        $item->delete();
    }

    public function toggleProduct(Wishlist $wishlist, Product $product): bool
    {
        $item = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->delete();

            return false;
        }

        $this->addProduct($wishlist, $product);

        return true;
    }

    public function moveToCart(WishlistItem $item, User $user): void
    {
        if (! $item->product->hasStock(1)) {
            throw new InsufficientStockException(
                "{$item->product->name} is currently out of stock."
            );
        }

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);

        DB::transaction(function () use ($cartService, $cart, $item) {
            $cartService->addProduct($cart, $item->product, 1);
            $this->removeProduct($item);
        });
    }
}
