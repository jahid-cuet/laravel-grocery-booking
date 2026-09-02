<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('messages.app_name') }} — {{ __('messages.tagline') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Hind Siliguri"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col selection:bg-brand-500 selection:text-white">

    <!-- Header & Navigation -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo & Nav Links -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('store.index') }}" class="flex items-center gap-3 group">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">{{ __('messages.app_name') }}</span>
                            <span class="block text-xs font-semibold text-brand-600 tracking-wide uppercase">Grocery Booking</span>
                        </div>
                    </a>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('store.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('store.index') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('messages.catalog') }}
                        </a>
                        <a href="{{ route('store.orders') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('store.orders') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('messages.order_history') }}
                        </a>
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.groceries.index') }}" class="px-4 py-2 rounded-xl text-sm font-bold transition-colors {{ request()->routeIs('admin.*') ? 'bg-slate-900 text-white' : 'text-amber-700 hover:bg-amber-50' }} flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Admin Panel</span>
                                </a>
                            @endif
                        @endauth
                    </nav>
                </div>

                <!-- Actions: Language, User Badge / Auth Buttons, Cart -->
                <div class="flex items-center gap-3">
                    <!-- Language Switcher (Bonus Point) -->
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold">
                        <a href="{{ route('locale.switch', 'en') }}" class="px-2.5 py-1.5 rounded-lg transition-all {{ app()->getLocale() === 'en' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-900' }}">
                            EN
                        </a>
                        <a href="{{ route('locale.switch', 'bn') }}" class="px-2.5 py-1.5 rounded-lg transition-all {{ app()->getLocale() === 'bn' ? 'bg-white shadow-sm text-brand-700' : 'text-slate-500 hover:text-slate-900' }}">
                            বাং
                        </a>
                    </div>

                    @auth
                        <!-- Authenticated User Profile & Logout -->
                        <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                            <div class="text-right hidden sm:block text-xs">
                                <span class="block font-extrabold text-slate-800 leading-tight">{{ auth()->user()->name }}</span>
                                <span class="inline-block px-1.5 py-0.2 text-[10px] font-bold uppercase rounded {{ auth()->user()->isAdmin() ? 'bg-slate-900 text-amber-300' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ auth()->user()->isAdmin() ? __('messages.admin_badge') : __('messages.user_badge') }}
                                </span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}" class="inline-block">
                                @csrf
                                <button type="submit" title="Logout" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Guest Login Button -->
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm">
                            Sign In
                        </a>
                    @endauth

                    <!-- Cart Drawer Trigger Button -->
                    <button id="openCartBtn" onclick="toggleCartDrawer(true)" class="relative flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold shadow-md shadow-brand-500/20 active:scale-95 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="hidden sm:inline text-sm">{{ __('messages.cart') }}</span>
                        <span id="cartCountBadge" class="bg-white text-brand-700 font-extrabold text-xs px-2 py-0.5 rounded-full shadow">0</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Session Flash Banners -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-2xl flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- AJAX Slide-Over Cart Drawer -->
    <div id="cartDrawer" class="fixed inset-0 z-50 overflow-hidden hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="toggleCartDrawer(false)"></div>
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="pointer-events-auto w-screen max-w-md bg-white shadow-2xl flex flex-col">
                <!-- Drawer Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-extrabold text-slate-900">{{ __('messages.cart') }}</h2>
                    </div>
                    <button onclick="toggleCartDrawer(false)" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Drawer Items List -->
                <div id="cartItemsList" class="flex-1 overflow-y-auto px-6 py-4 divide-y divide-slate-100">
                    <!-- Populated by JS -->
                </div>

                <!-- Drawer Footer & Checkout -->
                <div id="cartFooter" class="border-t border-slate-200 px-6 py-5 bg-slate-50 space-y-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>{{ __('messages.subtotal') }}</span>
                            <span id="cartSubtotal" class="font-bold text-slate-900">$0.00</span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                            <span>{{ __('messages.total') }}</span>
                            <span id="cartTotal" class="text-brand-600 text-lg">$0.00</span>
                        </div>
                    </div>

                    <!-- Notes input -->
                    <div>
                        <label for="orderNotes" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('messages.notes') }}</label>
                        <input type="text" id="orderNotes" placeholder="{{ __('messages.notes_placeholder') }}" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <!-- Checkout Button -->
                    <button id="checkoutBtn" onclick="submitAjaxOrder()" class="w-full py-3.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg shadow-brand-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <span>{{ __('messages.place_booking') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Confirmation Modal -->
    <div id="successModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeSuccessModal()"></div>
            <div class="relative inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full p-8 text-center space-y-5">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900">{{ __('messages.booking_success') }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Your grocery booking has been locked and confirmed with safe stock deduction.</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('messages.order_number') }}</span>
                    <div id="modalOrderNumber" class="text-lg font-black text-brand-700 tracking-wider mt-0.5">ORD-XXXXXX</div>
                    <div id="modalOrderTotal" class="text-sm font-semibold text-slate-700 mt-1">Total: $0.00</div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('store.orders') }}" class="flex-1 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all">
                        {{ __('messages.order_history') }}
                    </a>
                    <button onclick="closeSuccessModal()" class="flex-1 py-3 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition-all">
                        Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 space-y-2">
            <p class="font-medium">Grocery Booking System · Built for PARAMETER-X Limited Assessment</p>
            <p class="text-slate-400">Architecture: JWT Auth + RBAC + Repository Pattern + Atomic Pessimistic Lock Concurrency</p>
        </div>
    </footer>

    <!-- Global Store State & AJAX Scripts -->
    <script>
        const API_JWT_TOKEN = @json(session('jwt_token') ?? ($jwtToken ?? ''));
        const CART_STORAGE_KEY = @json(auth()->check() ? 'grocery_cart_user_'.auth()->id() : 'grocery_cart_guest');
        let cart = JSON.parse(localStorage.getItem(CART_STORAGE_KEY) || '[]');

        function saveCart() {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
            renderCart();
        }

        function toggleCartDrawer(open) {
            const drawer = document.getElementById('cartDrawer');
            if (open) {
                drawer.classList.remove('hidden');
                renderCart();
            } else {
                drawer.classList.add('hidden');
            }
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-emerald-600 text-white' : type === 'error' ? 'bg-rose-600 text-white' : 'bg-slate-900 text-white';

            toast.className = `${bgClass} pointer-events-auto px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-xs font-bold transform transition-all duration-300 translate-y-4 opacity-0`;
            toast.innerHTML = `
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>${message}</span>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('translate-y-4', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // Live Stock Check Interaction (Section 5 AJAX Requirement)
        async function checkLiveStock(itemId, itemName) {
            const btn = document.getElementById(`stock-btn-${itemId}`);
            const badge = document.getElementById(`stock-badge-${itemId}`);
            const originalText = btn ? btn.innerHTML : '';

            if (btn) {
                btn.innerHTML = `<span class="animate-spin inline-block mr-1">↻</span> Checking...`;
                btn.disabled = true;
            }

            try {
                const res = await fetch(`/api/groceries/${itemId}`);
                if (!res.ok) throw new Error('Failed to fetch live stock');
                const data = await res.json();
                const item = data.data;

                if (badge) {
                    if (item.stock_quantity > 5) {
                        badge.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800";
                        badge.textContent = `In Stock (${item.stock_quantity})`;
                    } else if (item.stock_quantity > 0) {
                        badge.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800";
                        badge.textContent = `Low Stock (${item.stock_quantity} left)`;
                    } else {
                        badge.className = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800";
                        badge.textContent = "Out of Stock";
                    }
                }

                showToast(`Live stock for "${item.name}": ${item.stock_quantity} units available.`, 'success');
            } catch (err) {
                showToast(`Could not check stock: ${err.message}`, 'error');
            } finally {
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        }

        function addToCart(id, name, price, maxStock) {
            const qtyInput = document.getElementById(`qty-input-${id}`);
            const quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            if (maxStock <= 0) {
                showToast(`"${name}" is out of stock.`, 'error');
                return;
            }

            const existing = cart.find(item => item.id === id);
            const currentCartQty = existing ? existing.quantity : 0;

            if (currentCartQty + quantity > maxStock) {
                showToast(`Cannot add more than available stock (${maxStock} units).`, 'error');
                return;
            }

            if (existing) {
                existing.quantity += quantity;
            } else {
                cart.push({ id, name, price: parseFloat(price), quantity, maxStock });
            }

            saveCart();
            showToast(`Added ${quantity}x "${name}" to your booking cart!`, 'success');
        }

        function updateCartQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const newQty = item.quantity + delta;
            if (newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (newQty > item.maxStock) {
                showToast(`Maximum available stock reached (${item.maxStock}).`, 'error');
                return;
            } else {
                item.quantity = newQty;
            }
            saveCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            saveCart();
        }

        function renderCart() {
            const countBadge = document.getElementById('cartCountBadge');
            const list = document.getElementById('cartItemsList');
            const subtotalEl = document.getElementById('cartSubtotal');
            const totalEl = document.getElementById('cartTotal');
            const checkoutBtn = document.getElementById('checkoutBtn');

            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            if (countBadge) countBadge.textContent = totalItems;
            if (subtotalEl) subtotalEl.textContent = `$${totalPrice.toFixed(2)}`;
            if (totalEl) totalEl.textContent = `$${totalPrice.toFixed(2)}`;

            if (!list) return;

            if (cart.length === 0) {
                list.innerHTML = `
                    <div class="py-16 text-center space-y-3">
                        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500">{{ __('messages.cart_empty') }}</p>
                    </div>
                `;
                if (checkoutBtn) checkoutBtn.disabled = true;
                return;
            }

            if (checkoutBtn) checkoutBtn.disabled = false;

            list.innerHTML = cart.map(item => `
                <div class="py-4 flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-slate-900 truncate">${item.name}</h4>
                        <div class="text-xs text-slate-500 mt-0.5">$${item.price.toFixed(2)} each · Subtotal: <strong class="text-slate-800">$${(item.price * item.quantity).toFixed(2)}</strong></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center bg-slate-100 rounded-xl p-1 border border-slate-200">
                            <button onclick="updateCartQty(${item.id}, -1)" class="w-6 h-6 rounded-lg bg-white flex items-center justify-center text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50">-</button>
                            <span class="w-8 text-center text-xs font-bold text-slate-900">${item.quantity}</span>
                            <button onclick="updateCartQty(${item.id}, 1)" class="w-6 h-6 rounded-lg bg-white flex items-center justify-center text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50">+</button>
                        </div>
                        <button onclick="removeFromCart(${item.id})" class="text-rose-500 hover:text-rose-700 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        async function submitAjaxOrder() {
            if (cart.length === 0) {
                showToast('Cart is empty!', 'error');
                return;
            }

            if (!API_JWT_TOKEN) {
                showToast('Please sign in before placing an order.', 'error');
                window.location.href = @json(route('login'));
                return;
            }

            const checkoutBtn = document.getElementById('checkoutBtn');
            const originalText = checkoutBtn.innerHTML;
            checkoutBtn.innerHTML = `<span class="animate-spin inline-block mr-2">↻</span> Processing...`;
            checkoutBtn.disabled = true;

            const payload = {
                items: cart.map(i => ({ grocery_item_id: i.id, quantity: i.quantity })),
                notes: document.getElementById('orderNotes')?.value || null
            };

            try {
                const res = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${API_JWT_TOKEN}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok) {
                    const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Order failed');
                    throw new Error(errorMsg);
                }

                cart = [];
                saveCart();
                toggleCartDrawer(false);

                document.getElementById('modalOrderNumber').textContent = data.data.order_number;
                document.getElementById('modalOrderTotal').textContent = `Total: ${data.data.formatted_total} (${data.data.items_count} items)`;
                document.getElementById('successModal').classList.remove('hidden');

            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                checkoutBtn.innerHTML = originalText;
                checkoutBtn.disabled = false;
            }
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
            window.location.reload();
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
        });
    </script>
</body>
</html>
