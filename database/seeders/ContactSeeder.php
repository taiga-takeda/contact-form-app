<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        // 既存のカテゴリとタグのIDをすべて取得しておく
        $categoryIds = Category::pluck('id')->toArray();
        $tags = Tag::all();

        $faker = Factory::create('ja_JP');

        for ($i = 0; $i < 20; $i++) {
            // 20件のダミーデータを作成
            $contact = Contact::create([
                'category_id' => $faker->randomElement($categoryIds),
                'first_name' => $faker->lastName(),
                'last_name' => $faker->firstName(),
                'gender' => $faker->numberBetween(1, 3),
                'email' => $faker->safeEmail(),
                'tel' => '0'.$faker->numerify('#########'), // 10桁
                'address' => $faker->prefecture().$faker->ward().$faker->streetAddress(),
                'building' => $faker->secondaryAddress(),
                'detail' => $faker->realText(100),
            ]);

            // 要件：ランダムに1〜3件のタグを抽出し、中間テーブルに紐付け（attach）
            $randomTags = $tags->random($faker->numberBetween(1, 3));
            $contact->tags()->attach($randomTags->pluck('id')->toArray());
        }
    }
}
