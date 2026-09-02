<?php

namespace App\Services;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new OrderService instance.
     */
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Place a new booking order with transaction safety and concurrency locks.
     *
     * @param  array<int, array{grocery_item_id: int, quantity: int}>  $items
     *
     * @throws ValidationException
     */
    public function placeOrder(User $user, array $items, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($user, $items, $notes) {
            // Consolidate quantities if the same item is submitted multiple times
            $consolidatedItems = [];
            foreach ($items as $item) {
                $itemId = (int) $item['grocery_item_id'];
                $qty = (int) $item['quantity'];

                $consolidatedItems[$itemId] = ($consolidatedItems[$itemId] ?? 0) + $qty;
            }

            $orderItemsData = [];
            $totalAmount = 0.00;

            foreach ($consolidatedItems as $itemId => $requestedQuantity) {
                // 🔒 Pessimistic Lock (SELECT ... FOR UPDATE) to prevent race condition & overselling
                /** @var GroceryItem|null $groceryItem */
                $groceryItem = GroceryItem::where('id', $itemId)->lockForUpdate()->first();

                if (! $groceryItem || ! $groceryItem->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ["The selected grocery item (ID: {$itemId}) is no longer available."],
                    ]);
                }

                if ($groceryItem->stock_quantity < $requestedQuantity) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Insufficient stock for '{$groceryItem->name}'. Available: {$groceryItem->stock_quantity}, requested: {$requestedQuantity}.",
                        ],
                    ]);
                }

                $unitPrice = (float) $groceryItem->price;
                $subtotal = round($unitPrice * $requestedQuantity, 2);
                $totalAmount += $subtotal;

                // Deduct stock safely within transaction
                $groceryItem->decrement('stock_quantity', $requestedQuantity);

                $orderItemsData[] = [
                    'grocery_item_id' => $groceryItem->id,
                    'quantity' => $requestedQuantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $orderData = [
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'total_amount' => $totalAmount,
                'status' => Order::STATUS_CONFIRMED,
                'notes' => $notes,
            ];

            return $this->orderRepository->create($orderData, $orderItemsData);
        });
    }

    /**
     * Get paginated order history for a user.
     *
     * @return LengthAwarePaginator<Order>
     */
    public function getUserOrders(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->paginateForUser($user->id, $perPage);
    }

    /**
     * Get a specific order belonging to a user.
     */
    public function getUserOrder(User $user, int|string $orderId): Order
    {
        return $this->orderRepository->findOrFailForUser($user->id, $orderId);
    }
}
