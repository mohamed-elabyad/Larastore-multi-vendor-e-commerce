<x-mail::message>
<h1 style="text-align: center; font-size: 24px">
Congratulations! you have Order.
</h1>

<x-mail::button :url="url('/')">
View Website
</x-mail::button>

<h3 style="font-size: 20px; margin-bottom: 15px">
Order Summary
</h3>

<x-mail::table>
<table>
<tbody>
<tr>
<td>Order #</td>
<td>{{$order->id}}</td>
</tr>
<tr>
<td>Order Date</td>
<td>{{$order->created_at}}</td>
</tr>
<tr>
<td>Order Total</td>
<td>{{\Illuminate\Support\Number::currency($order->total_price)}}</td>
</tr>
<tr>
<td>Platform Fee</td>
<td>{{\Illuminate\Support\Number::currency($order->website_commission ?? 0)}}</td>
</tr>
<tr>
<td>Your Earnings</td>
<td>{{\Illuminate\Support\Number::currency($order->vendor_subtotal ?? 0)}}</td>
</tr>

</tbody>
</table>
</x-mail::table>

<hr>

<x-mail::table>
<table>
<thead>
<tr>
<th>Photo</th>
<th>Item</th>
<th>Quantity</th>
<th>Price</th>
</tr>
</thead>
<tbody>
@foreach ($order->orderItems as $orderItem)
<tr>
<td style="padding: 5px">
<img style="min-width: 60px; max-width: 60px;"
src="{{$orderItem->product->getImageForOptions($orderItem->variation_type_option_ids)}}" alt="">
</td>
<td style="padding: 5px">
{{$orderItem->product->title}}
</td>
<td style="text-align: center; padding: 5px">
{{$orderItem->quantity}}
</td>
<td style="text-align: right; padding: 5px">
{{\Illuminate\Support\Number::currency($orderItem->price)}}
</td>
</tr>
@endforeach
</tbody>
</table>
</x-mail::table>

<x-mail::panel>
        Thank you for having business with us.
</x-mail::panel>

        Thanks,
        {{ config('app.name') }}
</x-mail::message>
