<?php

namespace App\Services;

use App\Models\GroceryItem;
use App\Repositories\Contracts\GroceryItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GroceryService
{
    /**
     * Create a new GroceryService instance.
     */
    public function __construct(
        protected GroceryItemRepositoryInterface $groceryRepository
    ) {}

    /**
     * Get paginated grocery items with optional filters (for Admin).
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<GroceryItem>
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->groceryRepository->paginate($perPage, $filters);
    }

    /**
     * Get all grocery items without pagination.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, GroceryItem>
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->groceryRepository->all($filters);
    }

    /**
     * Get only active and in-stock grocery items (for Users).
     *
     * @return LengthAwarePaginator<GroceryItem>
     */
    public function getAvailableItems(int $perPage = 15): LengthAwarePaginator
    {
        return $this->groceryRepository->getAvailablePaginated($perPage);
    }

    /**
     * Find a grocery item by ID.
     */
    public function getById(int|string $id): GroceryItem
    {
        return $this->groceryRepository->findOrFail($id);
    }

    /**
     * Create a new grocery item.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): GroceryItem
    {
        return $this->groceryRepository->create($data);
    }

    /**
     * Update an existing grocery item.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): GroceryItem
    {
        return $this->groceryRepository->update($id, $data);
    }

    /**
     * Delete a grocery item.
     */
    public function delete(int|string $id): bool
    {
        return $this->groceryRepository->delete($id);
    }

    /**
     * Update stock level for a grocery item.
     *
     * @param  string  $operation  'set', 'increment', or 'decrement'
     */
    public function updateStock(int|string $id, int $quantity, string $operation = 'set'): GroceryItem
    {
        return $this->groceryRepository->updateStock($id, $quantity, $operation);
    }
}
