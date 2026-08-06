<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['content' => '商品に関するお問い合わせ'],
            ['content' => '採用に関するお問い合わせ'],
            ['content' => 'その他'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
