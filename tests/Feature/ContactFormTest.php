<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;

class ContactFormTest extends TestCase
{
    use RefreshDatabase; // テスト実行時にデータベースを一時的にリセットして汚さないようにする設定

    // 要件：お問い合わせフォーム入力ページが正常に表示されること
    public function test_contact_page_can_be_rendered(): void
    {
        Category::create(['content' => '商品のお届けについて']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }

    // 要件：バリデーション通過時にデータベースに保存され、タグが記録され、thanksへ行くこと
    public function test_contact_can_be_submitted(): void
    {
        $category = Category::create(['content' => 'その他']);
        $tag = Tag::create(['name' => '質問']);

        $formData = [
            'category_id' => $category->id,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'test@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都新宿区',
            'building'    => 'ビル1F',
            'detail'      => 'お問い合わせのテスト内容です。',
            'tags'        => [$tag->id] // フォームから送られたタグ
        ];

        // 宛先のルート名（contact.store）に合わせてPOST送信します
        $response = $this->post(route('contact.store'), $formData);

        // サンクスページへリダイレクトされるか
        $response->assertRedirect(route('contact.thanks'));

        // データベースにデータが保存されているか
        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com'
        ]);

        // 中間テーブルにタグが記録されているか
        $contact = Contact::where('email', 'test@example.com')->first();
        $this->assertTrue($contact->tags->contains($tag->id));
    }
}
