<?php

namespace Database\Seeders;

use App\Models\GroceryItem;
use Illuminate\Database\Seeder;

class GroceryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Organic Whole Milk 1L',
                'description' => 'Fresh, pasteurized organic whole milk rich in calcium and vitamin D.',
                'price' => 3.49,
                'stock_quantity' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Farm Fresh Brown Eggs (12 Pack)',
                'description' => 'Grade-A pasture-raised farm fresh brown eggs.',
                'price' => 4.99,
                'stock_quantity' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'Artisan Sourdough Bread 500g',
                'description' => 'Naturally fermented sourdough loaf with a crispy crust.',
                'price' => 5.25,
                'stock_quantity' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Aromatic Basmati Rice 5kg',
                'description' => 'Premium long-grain aged fragrant Basmati rice.',
                'price' => 14.99,
                'stock_quantity' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Extra Virgin Olive Oil 750ml',
                'description' => 'Cold-pressed extra virgin olive oil from Mediterranean olives.',
                'price' => 11.50,
                'stock_quantity' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Fresh Fuji Apples (1kg)',
                'description' => 'Crisp, juicy and sweet freshly harvested Fuji apples.',
                'price' => 3.99,
                'stock_quantity' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Organic Cavendish Bananas (1kg)',
                'description' => 'Naturally ripened organic sweet bananas.',
                'price' => 2.49,
                'stock_quantity' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Boneless Chicken Breast 1kg',
                'description' => 'Tender and lean skinless chicken breast fillets.',
                'price' => 8.99,
                'stock_quantity' => 35,
                'is_active' => true,
            ],
            [
                'name' => 'Wild Atlantic Salmon Fillet 500g',
                'description' => 'Fresh sustainably caught Atlantic salmon fillet portion.',
                'price' => 13.75,
                'stock_quantity' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Aged Cheddar Cheese 250g',
                'description' => 'Sharp and crumbly 12-month mature cheddar cheese.',
                'price' => 6.20,
                'stock_quantity' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Fresh Baby Spinach 200g (Out of Stock Demo)',
                'description' => 'Tender washed organic baby spinach leaves.',
                'price' => 2.99,
                'stock_quantity' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Seasonal Dragon Fruit (Inactive Demo)',
                'description' => 'Exotic sweet dragon fruit (currently inactive).',
                'price' => 7.50,
                'stock_quantity' => 10,
                'is_active' => false,
            ],
        ];

        foreach ($items as $item) {
            GroceryItem::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
