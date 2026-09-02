@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.order_history') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Review your past grocery bookings and transaction-safe stock allocations</p>
        </div>
        <a href="{{ route('store.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs transition-all w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Continue Shopping</span>
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl p-16 text-center border border-slate-200 shadow-sm space-y-4 max-w-xl mx-auto">
            <div class="w-20 h-20 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <h2 class="text-lg font-bold text-slate-900">{{ __('messages.no_orders') }}</h2>
            <p class="text-xs text-slate-500">Explore our catalogue and place your first grocery booking today.</p>
            <a href="{{ route('store.index') }}" class="inline-block px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-500/20 transition-all">
                Browse Grocery Catalogue
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden transition-all hover:border-slate-300">
                    
                    <!-- Order Card Header -->
                    <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center font-black text-sm shadow-inner">
                                #
                            </div>
                            <div>
                                <span class="font-extrabold text-slate-900 text-sm tracking-wide">{{ $order->order_number }}</span>
                                <span class="block text-[11px] text-slate-500">{{ $order->created_at?->format('M d, Y · h:i A') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                {{ $order->status }}
                            </span>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">{{ __('messages.total') }}</span>
                                <span class="text-base font-black text-slate-900">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items List -->
                    <div class="p-6 divide-y divide-slate-100">
                        @foreach($order->items as $orderItem)
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between text-xs sm:text-sm">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs">
                                        {{ $orderItem->quantity }}x
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-slate-800">{{ $orderItem->groceryItem?->name ?? 'Item' }}</h4>
                                        <span class="text-[11px] text-slate-400">Unit Price: ${{ number_format($orderItem->unit_price, 2) }}</span>
                                    </div>
                                </div>
                                <span class="font-bold text-slate-900">${{ number_format($orderItem->subtotal, 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if($order->notes)
                        <div class="bg-amber-50/60 px-6 py-3 border-t border-amber-100 text-xs text-amber-900 flex items-center gap-2">
                            <strong class="font-bold">Note:</strong> {{ $order->notes }}
                        </div>
                    @endif

                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-4">
                {{ $orders->links() }}
            </div>
        </div>
    @endif

</div>
@endsection
