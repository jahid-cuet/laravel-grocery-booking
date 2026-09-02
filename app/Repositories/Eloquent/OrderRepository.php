<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get paginated orders for a specific user with loaded items.
     *
     * @return LengthAwarePaginator<Order>
     */
    public function paginateForUser(int|string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::forUser($userId)
            ->with(['items.groceryItem'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find an order for a user by ID.
     */
    public function findForUser(int|string $userId, int|string $orderId): ?Order
    {
        return Order::forUser($userId)
            ->with(['items.groceryItem'])
            ->find($orderId);
    }

    /**
     * Find an order for a user by ID or fail.
     */
    public function findOrFailForUser(int|string $userId, int|string $orderId): Order
    {
        return Order::forUser($userId)
            ->with(['items.groceryItem'])
            ->where('id', $orderId)
            ->firstOrFail();
    }

    /**
     * Create a new order with its associated order items.
     *
     * @param  array<string, mixed>  $orderData
     * @param  array<int, array{grocery_item_id: int, quantity: int, unit_price: float, subtotal: float}>  $itemsData
     */
    public function create(array $orderData, array $itemsData): Order
    {
        $order = Order::create($orderData);
        $order->items()->createMany($itemsData);

        return $order->load('items.groceryItem');
    }
}
