<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grocery\StoreGroceryItemRequest;
use App\Http\Requests\Grocery\UpdateGroceryItemRequest;
use App\Http\Requests\Grocery\UpdateStockRequest;
use App\Http\Resources\GroceryItemResource;
use App\Services\GroceryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroceryItemController extends Controller
{
    /**
     * Create a new GroceryItemController instance.
     */
    public function __construct(
        protected GroceryService $groceryService
    ) {}

    /**
     * Display a listing of all grocery items with filters (Admin).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'is_active', 'in_stock']);
        $perPage = (int) $request->input('per_page', 15);

        $items = $this->groceryService->getAllPaginated($filters, $perPage);

        return GroceryItemResource::collection($items);
    }

    /**
     * Store a newly created grocery item in storage (Admin).
     */
    public function store(StoreGroceryItemRequest $request): JsonResponse
    {
        $item = $this->groceryService->create($request->validated());

        return (new GroceryItemResource($item))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified grocery item (Admin).
     */
    public function show(int|string $id): GroceryItemResource
    {
        $item = $this->groceryService->getById($id);

        return new GroceryItemResource($item);
    }

    /**
     * Update the specified grocery item in storage (Admin).
     */
    public function update(UpdateGroceryItemRequest $request, int|string $id): GroceryItemResource
    {
        $item = $this->groceryService->update($id, $request->validated());

        return new GroceryItemResource($item);
    }

    /**
     * Remove the specified grocery item from storage (Admin).
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->groceryService->delete($id);

        return response()->json([
            'message' => 'Grocery item removed successfully.',
        ]);
    }

    /**
     * Update inventory / stock levels for a grocery item (Admin).
     */
    public function updateStock(UpdateStockRequest $request, int|string $id): GroceryItemResource
    {
        $quantity = (int) $request->input('quantity');
        $operation = (string) $request->input('operation', 'set');

        $item = $this->groceryService->updateStock($id, $quantity, $operation);

        return new GroceryItemResource($item);
    }
}
