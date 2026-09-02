<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grocery\StoreGroceryItemRequest;
use App\Http\Requests\Grocery\UpdateGroceryItemRequest;
use App\Http\Requests\Grocery\UpdateStockRequest;
use App\Models\GroceryItem;
use App\Services\GroceryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GroceryItemController extends Controller
{
    /**
     * Create a new GroceryItemController instance.
     */
    public function __construct(
        protected GroceryService $groceryService
    ) {}

    /**
     * Display a listing of grocery items for Admin.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $filters = array_filter([
            'search' => $search,
            'is_active' => $request->input('is_active'),
        ]);

        $groceries = $this->groceryService->getAllPaginated($filters, 10);

        // Stats summary for admin dashboard widgets
        $totalItems = GroceryItem::count();
        $activeItems = GroceryItem::active()->count();
        $outOfStockItems = GroceryItem::where('stock_quantity', '<=', 0)->count();
        $totalStockUnits = (int) GroceryItem::sum('stock_quantity');

        return view('admin.groceries.index', [
            'groceries' => $groceries,
            'search' => $search,
            'totalItems' => $totalItems,
            'activeItems' => $activeItems,
            'outOfStockItems' => $outOfStockItems,
            'totalStockUnits' => $totalStockUnits,
        ]);
    }

    /**
     * Store a newly created grocery item.
     */
    public function store(StoreGroceryItemRequest $request): RedirectResponse
    {
        $item = $this->groceryService->create($request->validated());

        return redirect()->route('admin.groceries.index')
            ->with('success', "Grocery item '{$item->name}' added successfully!");
    }

    /**
     * Update an existing grocery item.
     */
    public function update(UpdateGroceryItemRequest $request, int|string $id): RedirectResponse
    {
        $item = $this->groceryService->update($id, $request->validated());

        return redirect()->route('admin.groceries.index')
            ->with('success', "Grocery item '{$item->name}' updated successfully!");
    }

    /**
     * Remove a grocery item (Soft delete).
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $item = $this->groceryService->getById($id);
        $this->groceryService->delete($id);

        return redirect()->route('admin.groceries.index')
            ->with('success', "Grocery item '{$item->name}' removed successfully.");
    }

    /**
     * Update stock level for a grocery item.
     */
    public function updateStock(UpdateStockRequest $request, int|string $id): RedirectResponse
    {
        $quantity = (int) $request->input('quantity');
        $operation = (string) $request->input('operation', 'set');

        $item = $this->groceryService->updateStock($id, $quantity, $operation);

        return redirect()->route('admin.groceries.index')
            ->with('success', "Stock for '{$item->name}' updated to {$item->stock_quantity} units.");
    }
}
