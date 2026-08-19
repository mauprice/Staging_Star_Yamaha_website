@extends('yamaha.layout')

@section('title', 'My Orders')

@section('content')

<div class="bg-ink border-b-2 border-brand">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
            <a href="{{ route('dashboard') }}" class="hover:text-white transition">My Account</a>
            <span class="mx-2">›</span>
            <span class="text-white">Orders</span>
        </nav>
        <h1 class="text-4xl font-black uppercase text-white tracking-tight">My Orders</h1>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if($orders->isEmpty())
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-8 text-center text-gray-400">
        You haven't placed any orders yet.
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        @foreach($orders as $order)
        <a href="{{ route('yamaha.account.orders.show', $order) }}"
           class="flex items-center justify-between p-5 hover:bg-gray-50 transition {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
            <div>
                <p class="font-black text-gray-900 text-sm">{{ $order->order_number }}</p>
                <p class="text-xs text-gray-400">{{ $order->placed_at?->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-black uppercase tracking-wide px-2.5 py-1 rounded-full
                    {{ $order->status->color() === 'success' ? 'bg-green-100 text-green-700' : ($order->status->color() === 'danger' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ $order->status->label() }}
                </span>
                <span class="font-black text-gray-900">${{ number_format($order->total, 2) }}</span>
            </div>
        </a>
        @endforeach
    </div>
    {{ $orders->links() }}
    @endif

</div>

@endsection
