<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => '既卒'],
            ['name' => '新卒'],
            ['name' => '重要'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
