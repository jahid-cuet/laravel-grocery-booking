@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

    <!-- Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-900 via-brand-900 to-slate-900 text-white p-8 sm:p-12 shadow-2xl">
        <div class="relative z-10 max-w-2xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 border border-brand-400/30 text-brand-300 text-xs font-bold tracking-wide uppercase">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ __('messages.tagline') }}
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                {{ __('messages.catalog') }}
            </h1>
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                Browse our farm-fresh groceries. Add items to your booking cart and experience instant, concurrency-safe checkout with real-time live stock validation.
            </p>

            <!-- Search Form -->
            <form method="GET" action="{{ route('store.index') }}" class="pt-2 flex gap-2 max-w-md">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('messages.search_placeholder') }}" class="w-full pl-10 pr-4 py-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl text-white placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button type="submit" class="px-5 py-3 rounded-2xl bg-brand-500 hover:bg-brand-400 text-slate-950 font-bold text-xs sm:text-sm transition-all shadow-md">
                    Search
                </button>
            </form>
        </div>

        <!-- Decorative background shapes -->
        <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Groceries Grid Section -->
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900">{{ __('messages.available_items') }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Showing fresh products updated in real-time</p>
            </div>
            @if($search)
                <a href="{{ route('store.index') }}" class="text-xs font-bold text-rose-600 hover:underline flex items-center gap-1">
                    <span>Clear Search</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            @endif
        </div>

        @if($groceries->isEmpty())
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm space-y-3">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No grocery items found</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">We couldn't find any grocery items matching your query. Try searching with different keywords.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($groceries as $item)
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm hover:shadow-xl hover:border-brand-200 transition-all duration-300 flex flex-col justify-between group">
                        
                        <!-- Card Top: Category, Status Badge & Title -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Grocery Item #{{ $item->id }}</span>
                                
                                <!-- Stock Badge with unique ID for AJAX updates -->
                                <span id="stock-badge-{{ $item->id }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold 
                                    @if($item->stock_quantity > 5) bg-emerald-100 text-emerald-800
                                    @elseif($item->stock_quantity > 0) bg-amber-100 text-amber-800
                                    @else bg-rose-100 text-rose-800
                                    @endif">
                                    @if($item->stock_quantity > 5)
                                        {{ __('messages.in_stock') }} ({{ $item->stock_quantity }})
                                    @elseif($item->stock_quantity > 0)
                                        {{ __('messages.low_stock') }} ({{ $item->stock_quantity }})
                                    @else
                                        {{ __('messages.out_of_stock') }}
                                    @endif
                                </span>
                            </div>

                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base group-hover:text-brand-700 transition-colors line-clamp-1">
                                    {{ $item->name }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $item->description ?? 'Fresh, high-quality grocery item sourced directly from local producers.' }}
                                </p>
                            </div>
                        </div>

                        <!-- Card Bottom: Price, Live Stock Check AJAX button, and Add-to-Cart -->
                        <div class="pt-5 mt-4 border-t border-slate-100 space-y-3">
                            <div class="flex items-baseline justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">{{ __('messages.unit_price') }}</span>
                                    <span class="text-xl font-black text-slate-900">${{ number_format($item->price, 2) }}</span>
                                </div>

                                <!-- AJAX Live Stock Check Button (Section 5 Requirement) -->
                                <button id="stock-btn-{{ $item->id }}" onclick="checkLiveStock({{ $item->id }}, '{{ addslashes($item->name) }}')" type="button" class="text-[11px] font-bold text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-2.5 py-1.5 rounded-xl transition-all">
                                    {{ __('messages.check_stock') }}
                                </button>
                            </div>

                            <!-- Add to Cart AJAX Controls -->
                            <div class="flex items-center gap-2">
                                <div class="w-20">
                                    <input type="number" id="qty-input-{{ $item->id }}" min="1" max="{{ max(1, $item->stock_quantity) }}" value="1" {{ $item->stock_quantity <= 0 ? 'disabled' : '' }} class="w-full text-center text-xs font-bold py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none disabled:opacity-50">
                                </div>
                                <button onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, {{ $item->stock_quantity }})" {{ $item->stock_quantity <= 0 ? 'disabled' : '' }} class="flex-1 py-2 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all {{ $item->stock_quantity > 0 ? 'bg-slate-900 hover:bg-brand-600 text-white shadow-sm active:scale-95' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <span>{{ $item->stock_quantity > 0 ? __('messages.add_to_cart') : __('messages.out_of_stock') }}</span>
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-6">
                {{ $groceries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
