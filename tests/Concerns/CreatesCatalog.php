<?php

namespace Tests\Concerns;

use App\Models\Category;

trait CreatesCatalog
{
    private function subcategory(string $name = '3 Seater Sofas', string $room = 'Living Room', string $status = 'active'): Category
    {
        $parent = Category::query()->create([
            'name' => $room,
            'slug' => str($room)->slug()->toString().'-'.uniqid(),
            'status' => 'active',
            'sort_order' => 1,
        ]);

        return Category::query()->create([
            'parent_id' => $parent->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString().'-'.uniqid(),
            'menu_group' => 'Sofas & Seating',
            'status' => $status,
            'sort_order' => 1,
        ]);
    }
}
