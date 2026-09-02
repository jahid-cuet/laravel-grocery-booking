<?php

namespace App\Models;

use Database\Factories\GroceryItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroceryItem extends Model
{
    /** @use HasFactory<GroceryItemFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active items.
     *
     * @param  Builder<GroceryItem>  $query
     * @return Builder<GroceryItem>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include items with stock available.
     *
     * @param  Builder<GroceryItem>  $query
     * @return Builder<GroceryItem>
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to only include active and in-stock items.
     *
     * @param  Builder<GroceryItem>  $query
     * @return Builder<GroceryItem>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->active()->inStock();
    }

    /**
     * Determine if the item is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Determine if the item has sufficient stock for a given quantity.
     */
    public function hasSufficientStock(int $quantity): bool
    {
        return $quantity > 0 && $this->stock_quantity >= $quantity;
    }
}
