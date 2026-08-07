<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class ContactModelTest extends TestCase
{
    use RefreshDatabase;

    // 要件：1つのカテゴリから紐づく複数のお問い合わせ（hasMany）が取得できること
    public function test_category_has_many_contacts(): void
    {
        $category = Category::create(['content' => '商品トラブル']);
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => 'テスト', 'last_name' => 'ユーザー', 'gender' => 1,
            'email' => 'u@example.com', 'tel' => '09000000000', 'address' => '住所', 'detail' => '詳細'
        ]);

        $this->assertTrue($category->contacts->contains($contact));
    }

    // 要件：1つのお問い合わせから特定のカテゴリ（belongsTo）や紐づくタグ（belongsToMany）が取得できること
    public function test_contact_relations_are_working(): void
    {
        $category = Category::create(['content' => 'その他']);
        $tag = Tag::create(['name' => '不具合報告']);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => 'テスト', 'last_name' => 'ユーザー', 'gender' => 1,
            'email' => 'u2@example.com', 'tel' => '09000000000', 'address' => '住所', 'detail' => '詳細'
        ]);

        $contact->tags()->attach($tag->id);

        // リレーションシップの確認
        $this->assertEquals($category->id, $contact->category->id);
        $this->assertTrue($contact->tags->contains($tag));
    }
}
