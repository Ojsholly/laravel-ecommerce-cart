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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling checkout operations and order creation.
 */
class CheckoutService
{
    public function __construct(
        private PriceCalculationService $priceCalculationService
    ) {}

    /**
     * Process checkout for a cart and create an order.
     *
     * @throws EmptyCartException
     * @throws InsufficientStockException
     */
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
                'status' => OrderStatus::COMPLETED,
            ]);

            foreach ($availableItems as $item) {
                $this->createOrderItem($order, $item);
                $this->decrementStock($item->product, $item->quantity);
            }

            $this->removeAvailableItemsFromCart($cart, $availableItems);

            return $order;
        });
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, CartItem>, 1: \Illuminate\Support\Collection<int, CartItem>}
     */
    private function validateStock(Cart $cart): array
    {
        $availableItems = collect();
        $unavailableItems = collect();

        foreach ($cart->items as $item) {
            /** @var CartItem $item */
            $product = Product::lockForUpdate()->find($item->product_id);

            if ($product && $product->hasStock($item->quantity)) {
                $availableItems->push($item);
            } else {
                $unavailableItems->push($item);
            }
        }

        return [$availableItems, $unavailableItems];
    }

    private function createOrderItem(Order $order, CartItem $cartItem): OrderItem
    {
        /** @var Product $product */
        $product = $cartItem->product;

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $cartItem->product_id,
            'quantity' => $cartItem->quantity,
            'price_snapshot' => $product->price,
            'product_snapshot' => $product->toSnapshot(),
        ]);
    }

    private function removeAvailableItemsFromCart(Cart $cart, Collection $availableItems): void
    {
        $availableItemIds = $availableItems->pluck('id');
        $cart->items()->whereIn('id', $availableItemIds)->delete();
    }

    private function decrementStock(Product $product, int $quantity): void
    {
        $lowStockThreshold = config('cart.low_stock_threshold', 10);
        $stockBeforeDecrement = $product->stock_quantity;

        $product->decrement('stock_quantity', $quantity);

        $stockAfterDecrement = $product->fresh()->stock_quantity;

        if ($stockBeforeDecrement > $lowStockThreshold && $stockAfterDecrement <= $lowStockThreshold) {
            SendLowStockNotification::dispatch($product);
        }
    }
}
