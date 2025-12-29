<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Jobs\SendLowStockNotification;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private PriceCalculationService $priceCalculationService,
        private CartService $cartService
    ) {}

    public function processCheckout(Cart $cart): Order
    {
        return DB::transaction(function () use ($cart) {
            $cart->load(['items.product']);

            if ($cart->items->isEmpty()) {
                throw new EmptyCartException;
            }

            [$availableItems, $unavailableItems] = $this->validateStock($cart);

            if ($availableItems->isEmpty()) {
                throw new InsufficientStockException('All items in your cart are out of stock.');
            }

            $pricing = $this->priceCalculationService->calculateOrderPricing($availableItems);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $cart->user_id,
                'subtotal' => $pricing->subtotal,
                'vat_rate' => $pricing->breakdown['vat']['rate'] ?? config('cart.vat_rate', 7.5),
                'vat_amount' => $pricing->breakdown['vat']['amount'] ?? '0.00',
                'total' => $pricing->total,
                'pricing_breakdown' => $pricing->breakdown,
                'status' => OrderStatus::PENDING,
            ]);

            foreach ($availableItems as $item) {
                $this->createOrderItem($order, $item);
                $this->decrementStock($item->product, $item->quantity);
            }

            $this->cartService->clearCart($cart);

            return $order;
        });
    }

    private function validateStock(Cart $cart): array
    {
        $availableItems = collect();
        $unavailableItems = collect();

        foreach ($cart->items as $item) {
            $product = Product::lockForUpdate()->find($item->product_id);

            if ($product->hasStock($item->quantity)) {
                $availableItems->push($item);
            } else {
                $unavailableItems->push($item);
            }
        }

        return [$availableItems, $unavailableItems];
    }

    private function createOrderItem(Order $order, CartItem $cartItem): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $cartItem->product_id,
            'quantity' => $cartItem->quantity,
            'price_snapshot' => $cartItem->product->price,
            'product_snapshot' => $cartItem->product->toSnapshot(),
        ]);
    }

    private function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock_quantity', $quantity);

        if ($product->fresh()->isLowStock()) {
            SendLowStockNotification::dispatch($product);
        }
    }
}
