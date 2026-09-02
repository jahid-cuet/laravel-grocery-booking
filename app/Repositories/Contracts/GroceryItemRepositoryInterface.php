<?php

namespace App\Repositories\Contracts;

use App\Models\GroceryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface GroceryItemRepositoryInterface
{
    /**
     * Get all grocery items with optional filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, GroceryItem>
     */
    public function all(array $filters = []): Collection;

    /**
     * Get paginated grocery items with optional filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<GroceryItem>
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find a grocery item by ID.
     */
    public function find(int|string $id): ?GroceryItem;

    /**
     * Find a grocery item by ID or fail.
     */
    public function findOrFail(int|string $id): GroceryItem;

    /**
     * Create a new grocery item.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): GroceryItem;

    /**
     * Update an existing grocery item.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(GroceryItem|int|string $item, array $data): GroceryItem;

    /**
     * Delete a grocery item.
     */
    public function delete(GroceryItem|int|string $item): bool;

    /**
     * Update stock quantity for a grocery item.
     *
     * @param  string  $operation  'set', 'increment', or 'decrement'
     */
    public function updateStock(GroceryItem|int|string $item, int $quantity, string $operation = 'set'): GroceryItem;

    /**
     * Get only active and in-stock grocery items for users.
     *
     * @return LengthAwarePaginator<GroceryItem>
     */
    public function getAvailablePaginated(int $perPage = 15): LengthAwarePaginator;
}
