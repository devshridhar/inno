<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = config('news.categories');

        foreach ($categories as $slug => $categoryData) {
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $categoryData['name'],
                    'description' => "Articles related to {$categoryData['name']}",
                    'color' => $categoryData['color'],
                    'is_active' => true,
                    'sort_order' => array_search($slug, array_keys($categories))
                ]
            );
        }
    }
}
