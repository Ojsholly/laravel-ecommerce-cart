<x-mail::message>
# Low Stock Alert

The following product is running low on stock:

**Product:** {{ $product->name }}  
**Current Stock:** {{ $product->stock_quantity }} units  
**Threshold:** {{ config('cart.low_stock_threshold', 10) }} units

Please consider restocking this item soon to avoid running out of inventory.

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
