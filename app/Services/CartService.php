<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public function addProduct(Cart $cart, Product $product, int $quantity = 1): CartItem
    {
        return DB::transaction(function () use ($cart, $product, $quantity) {
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            $newQuantity = $cartItem ? $cartItem->quantity + $quantity : $quantity;

            if (! $product->hasStock($newQuantity)) {
                throw new InsufficientStockException(
                    "Only {$product->stock_quantity} units available for {$product->name}."
                );
            }

            return CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                ],
                ['quantity' => $newQuantity]
            );
        });
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $this->removeItem($item);

            return $item;
        }

        if (! $item->product->hasStock($quantity)) {
            throw new InsufficientStockException(
                "Only {$item->product->stock_quantity} units available for {$item->product->name}."
            );
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function moveToWishlist(CartItem $item, User $user): void
    {
        $wishlistService = app(WishlistService::class);
        $wishlist = $wishlistService->getOrCreateWishlist($user);

        DB::transaction(function () use ($wishlistService, $wishlist, $item) {
            $wishlistService->addProduct($wishlist, $item->product);
            $this->removeItem($item);
        });
    }
}
