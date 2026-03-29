<x-mail::message>
<h1 style="text-align: center; font-size: 24px;">Payment was completed successfully.</h1>

@foreach ($orders as $order)
<x-mail::table>
<table>
<tbody>
<tr>
<td>Seller</td>
<td>
<a href="{{route('vendor.profile', $order->vendor->store_name)}}">
{{$order->vendor->store_name}}</a>
</td>
</tr>
<tr>
<td>Order #</td>
<td>#{{$order->id}}</td>
</tr>
<tr>
<td>Items</td>
<td>{{$order->orderItems->count()}}</td>
</tr>
<tr>
<td>Total</td>
<td>{{\Illuminate\Support\Number::currency($order->total_price)}}</td>
</tr>
</tbody>
</table>
</x-mail::table>

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
<td style="font-size: 13px; padding: 5px">
{{$orderItem->product->title}}
</td>
<td>
{{$orderItem->quantity}}
</td>
<td>
{{\Illuminate\Support\Number::currency($orderItem->price)}}
</td>
</tr>
@endforeach
</tbody>
</table>
</x-mail::table>

<x-mail::button :url="url('/')">
View Website
</x-mail::button>
@endforeach

<x-mail::panel>
        Thanks For Shopping with us.
</x-mail::panel>

        Thanks,
        {{config('app.name')}}
</x-mail::message>
