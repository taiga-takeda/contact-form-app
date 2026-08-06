<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 1〜3のランダムなカテゴリIDを紐づける
            'category_id' => fake()->numberBetween(1, 3), 
            'first_name'  => fake()->lastName(), // 姓
            'last_name'   => fake()->firstName(), // 名
            'gender'      => fake()->numberBetween(1, 3), // 1:男性, 2:女性, 3:その他
            'email'       => fake()->safeEmail(),
            'tel'         => '0' . fake()->numerify('#########'), // ハイフンなし10桁
            'address'     => fake()->address(),
            'building'    => fake()->secondaryAddress(),
            'detail'      => fake()->realText(100), // 100文字程度のダミー本文
        ];
    }
}
