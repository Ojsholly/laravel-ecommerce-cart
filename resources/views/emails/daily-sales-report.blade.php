<x-mail::message>
# Daily Sales Report - {{ $date }}

Here's your sales summary for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}:

**Total Orders:** {{ number_format($stats['total_orders']) }}  
**Total Revenue:** ${{ number_format($stats['total_revenue'], 2) }}  
**Total Items Sold:** {{ number_format($stats['total_items']) }}

@if($stats['total_orders'] > 0)
## Recent Orders (Last 10)

@foreach($recentOrders as $order)
- **Order #{{ $order->order_number }}** - ${{ number_format($order->total, 2) }} at {{ $order->created_at->format('h:i A') }}
@endforeach

@if($stats['total_orders'] > 10)
*Showing 10 of {{ number_format($stats['total_orders']) }} orders*
@endif
@else
No orders were placed on this date.
@endif

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
