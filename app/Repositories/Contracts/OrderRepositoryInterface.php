<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Get paginated orders for a specific user with loaded items.
     *
     * @return LengthAwarePaginator<Order>
     */
    public function paginateForUser(int|string $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an order for a user by ID.
     */
    public function findForUser(int|string $userId, int|string $orderId): ?Order;

    /**
     * Find an order for a user by ID or fail.
     */
    public function findOrFailForUser(int|string $userId, int|string $orderId): Order;

    /**
     * Create a new order with its associated order items.
     *
     * @param  array<string, mixed>  $orderData
     * @param  array<int, array{grocery_item_id: int, quantity: int, unit_price: float, subtotal: float}>  $itemsData
     */
    public function create(array $orderData, array $itemsData): Order;
}
