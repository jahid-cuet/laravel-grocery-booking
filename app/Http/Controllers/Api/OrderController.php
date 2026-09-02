<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /**
     * Create a new OrderController instance.
     */
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display a listing of the authenticated user's orders.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->input('per_page', 15);
        $orders = $this->orderService->getUserOrders($request->user(), $perPage);

        return OrderResource::collection($orders);
    }

    /**
     * Place a new order for the authenticated user.
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->placeOrder(
            user: $request->user(),
            items: $request->validated('items'),
            notes: $request->validated('notes'),
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a specific order belonging to the authenticated user.
     */
    public function show(Request $request, int|string $id): OrderResource
    {
        $order = $this->orderService->getUserOrder($request->user(), $id);

        return new OrderResource($order);
    }
}
