<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Roasted Banana Matcha', 'description' => 'Matcha whisked with caramelized roasted banana.', 'price' => 130, 'image' => '/banamatcha.webp'],
            ['name' => 'Chai Matcha', 'description' => 'Matcha layered with spiced chai and steamed milk.', 'price' => 150, 'image' => '/chaimatcha.jpg'],
            ['name' => 'Strawberry Matcha', 'description' => 'Iced matcha over fresh strawberry puree and milk.', 'price' => 175, 'image' => '/stramatcha.jpg'],
        ];

        foreach ($items as $index => $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                $item + ['sort_order' => $index, 'is_available' => true],
            );
        }
    }
}
