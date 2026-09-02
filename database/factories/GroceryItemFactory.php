<?php

namespace Database\Factories;

use App\Models\GroceryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryItem>
 */
class GroceryItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<GroceryItem>
     */
    protected $model = GroceryItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $groceries = [
            'Fresh Whole Milk 1L',
            'Farm Fresh Eggs (12 pcs)',
            'Organic Brown Rice 5kg',
            'Whole Wheat Bread 400g',
            'Pure Sunflower Oil 2L',
            'Fuji Apples 1kg',
            'Fresh Bananas (1 Dozen)',
            'Red Onions 1kg',
            'Fresh Potatoes 2kg',
            'Organic Green Tea 100g',
            'Cheddar Cheese 200g',
            'Extra Virgin Olive Oil 500ml',
            'Chicken Breast Fillet 1kg',
            'Fresh Salmon Fillet 500g',
            'Greek Yogurt 500g',
        ];

        return [
            'name' => fake()->unique()->randomElement($groceries).' - '.fake()->unique()->numberBetween(1, 999),
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 1, 50),
            'stock_quantity' => fake()->numberBetween(10, 100),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the grocery item is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Indicate that the grocery item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the grocery item has low stock.
     */
    public function lowStock(int $quantity = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $quantity,
        ]);
    }
}
