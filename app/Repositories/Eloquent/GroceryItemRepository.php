<?php

namespace App\Repositories\Eloquent;

use App\Models\GroceryItem;
use App\Repositories\Contracts\GroceryItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GroceryItemRepository implements GroceryItemRepositoryInterface
{
    /**
     * Get all grocery items with optional filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, GroceryItem>
     */
    public function all(array $filters = []): Collection
    {
        return $this->applyFilters($filters)->get();
    }

    /**
     * Get paginated grocery items with optional filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<GroceryItem>
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters($filters)->latest()->paginate($perPage);
    }

    /**
     * Find a grocery item by ID.
     */
    public function find(int|string $id): ?GroceryItem
    {
        return GroceryItem::find($id);
    }

    /**
     * Find a grocery item by ID or fail.
     */
    public function findOrFail(int|string $id): GroceryItem
    {
        return GroceryItem::findOrFail($id);
    }

    /**
     * Find an active grocery item by ID or fail.
     */
    public function findActiveOrFail(int|string $id): GroceryItem
    {
        return GroceryItem::active()->findOrFail($id);
    }

    /**
     * Create a new grocery item.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): GroceryItem
    {
        return GroceryItem::create($data);
    }

    /**
     * Update an existing grocery item.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(GroceryItem|int|string $item, array $data): GroceryItem
    {
        $model = $item instanceof GroceryItem ? $item : $this->findOrFail($item);
        $model->update($data);

        return $model->fresh();
    }

    /**
     * Delete a grocery item.
     */
    public function delete(GroceryItem|int|string $item): bool
    {
        $model = $item instanceof GroceryItem ? $item : $this->findOrFail($item);

        return (bool) $model->delete();
    }

    /**
     * Update stock quantity for a grocery item.
     *
     * @param  string  $operation  'set', 'increment', or 'decrement'
     */
    public function updateStock(GroceryItem|int|string $item, int $quantity, string $operation = 'set'): GroceryItem
    {
        return DB::transaction(function () use ($item, $quantity, $operation) {
            $id = $item instanceof GroceryItem ? $item->getKey() : $item;
            $model = GroceryItem::whereKey($id)->lockForUpdate()->firstOrFail();

            match ($operation) {
                'set' => $model->stock_quantity = max(0, $quantity),
                'increment' => $model->stock_quantity += $quantity,
                'decrement' => $model->stock_quantity = max(0, $model->stock_quantity - $quantity),
                default => throw new InvalidArgumentException("Unsupported stock operation: {$operation}"),
            };

            $model->save();

            return $model->fresh();
        });
    }

    /**
     * Get only active and in-stock grocery items for users.
     *
     * @return LengthAwarePaginator<GroceryItem>
     */
    public function getAvailablePaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters($filters)->available()->latest()->paginate($perPage);
    }

    /**
     * Apply filtering parameters to the query builder.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<GroceryItem>
     */
    protected function applyFilters(array $filters): Builder
    {
        $query = GroceryItem::query();

        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['in_stock']) && filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN)) {
            $query->inStock();
        }

        return $query;
    }
}
