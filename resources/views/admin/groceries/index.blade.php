@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Admin Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 text-white text-[11px] font-bold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Admin Inventory Management
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Grocery Catalogue & Stock</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Add items, adjust prices, and control real-time inventory levels</p>
        </div>

        <!-- Add Item Button -->
        <button onclick="openModal('createItemModal')" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-brand-500/25 active:scale-95 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Grocery Item</span>
        </button>
    </div>

    <!-- Stats Widgets -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Items</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900">{{ $totalItems }}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Active Items</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-700">{{ $activeItems }}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Out of Stock</span>
            <div class="text-2xl sm:text-3xl font-black text-rose-600">{{ $outOfStockItems }}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold text-brand-600 uppercase tracking-wider">Total Stock Units</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($totalStockUnits) }}</div>
        </div>
    </div>

    <!-- Items Table & Filters -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
        
        <!-- Table Search Bar -->
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row gap-3 items-center justify-between">
            <form method="GET" action="{{ route('admin.groceries.index') }}" class="w-full sm:w-80 flex gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search grocery items..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold">Search</button>
            </form>

            @if($search)
                <a href="{{ route('admin.groceries.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Clear Search</a>
            @endif
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Item Details</th>
                        <th class="px-6 py-4">Unit Price</th>
                        <th class="px-6 py-4">Stock Level</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold">
                    @forelse($groceries as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $item->name }}</div>
                                <div class="text-[11px] text-slate-400 line-clamp-1 max-w-xs">{{ $item->description ?? 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 font-black text-slate-900 text-sm">
                                ${{ number_format($item->price, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold 
                                    @if($item->stock_quantity > 5) bg-emerald-100 text-emerald-800
                                    @elseif($item->stock_quantity > 0) bg-amber-100 text-amber-800
                                    @else bg-rose-100 text-rose-800
                                    @endif">
                                    {{ $item->stock_quantity }} units
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $item->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <!-- Stock Adjust Button -->
                                <button onclick="openStockModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->stock_quantity }})" class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-all">
                                    Adjust Stock
                                </button>

                                <!-- Edit Button -->
                                <button onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->description ?? '') }}', {{ $item->price }}, {{ $item->stock_quantity }}, {{ $item->is_active ? 'true' : 'false' }})" class="px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition-all">
                                    Edit
                                </button>

                                <!-- Delete Form -->
                                <form method="POST" action="{{ route('admin.groceries.destroy', $item->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-all">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                No grocery items found in catalogue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-5 border-t border-slate-200">
            {{ $groceries->links() }}
        </div>
    </div>

</div>

<!-- Modal 1: Create Grocery Item -->
<div id="createItemModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('createItemModal')"></div>
        <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-lg font-extrabold text-slate-900">Add New Grocery Item</h3>
                <button onclick="closeModal('createItemModal')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.groceries.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Item Name</label>
                    <input type="text" name="name" required placeholder="e.g. Fresh Organic Strawberries 500g" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (Optional)</label>
                    <textarea name="description" rows="2" placeholder="Item description, origin, organic certifications..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Price ($)</label>
                        <input type="number" step="0.01" min="0.01" name="price" required placeholder="4.99" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Initial Stock</label>
                        <input type="number" min="0" name="stock_quantity" required placeholder="50" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="create_active" name="is_active" value="1" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <label for="create_active" class="text-xs font-bold text-slate-700">Active & Visible in Storefront</label>
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('createItemModal')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Grocery Item -->
<div id="editItemModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('editItemModal')"></div>
        <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-lg font-extrabold text-slate-900">Edit Grocery Item</h3>
                <button onclick="closeModal('editItemModal')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form id="editItemForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Item Name</label>
                    <input type="text" id="edit_name" name="name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (Optional)</label>
                    <textarea id="edit_description" name="description" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Price ($)</label>
                        <input type="number" step="0.01" min="0.01" id="edit_price" name="price" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Stock Quantity</label>
                        <input type="number" min="0" id="edit_stock" name="stock_quantity" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <label for="edit_is_active" class="text-xs font-bold text-slate-700">Active & Visible in Storefront</label>
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('editItemModal')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md shadow-brand-500/20">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Adjust Stock -->
<div id="stockModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('stockModal')"></div>
        <div class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-lg font-extrabold text-slate-900">Manage Inventory Stock</h3>
                <button onclick="closeModal('stockModal')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form id="stockForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Item Name</span>
                    <div id="stockItemName" class="font-extrabold text-slate-900 text-sm mt-0.5"></div>
                    <div id="stockCurrent" class="text-xs font-semibold text-slate-500 mt-0.5"></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Operation</label>
                    <select name="operation" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="set">Set Exact Stock Quantity</option>
                        <option value="increment">Add to Existing Stock (+)</option>
                        <option value="decrement">Reduce from Stock (-)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Quantity</label>
                    <input type="number" min="0" name="quantity" required placeholder="10" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('stockModal')" class="flex-1 py-3 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-500/20">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function openEditModal(id, name, description, price, stock, isActive) {
        document.getElementById('editItemForm').action = `/admin/groceries/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_stock').value = stock;
        document.getElementById('edit_is_active').checked = isActive;
        openModal('editItemModal');
    }
    function openStockModal(id, name, currentStock) {
        document.getElementById('stockForm').action = `/admin/groceries/${id}/stock`;
        document.getElementById('stockItemName').textContent = name;
        document.getElementById('stockCurrent').textContent = `Current Stock: ${currentStock} units`;
        openModal('stockModal');
    }
</script>
@endsection
