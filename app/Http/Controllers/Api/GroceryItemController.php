<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroceryItemResource;
use App\Services\GroceryService;
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
     * Display a listing of available (active & in-stock) grocery items for users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->input('per_page', 15);
        $items = $this->groceryService->getAvailableItems($perPage);

        return GroceryItemResource::collection($items);
    }

    /**
     * Display the specified grocery item.
     */
    public function show(int|string $id): GroceryItemResource
    {
        $item = $this->groceryService->getById($id);

        return new GroceryItemResource($item);
    }
}
