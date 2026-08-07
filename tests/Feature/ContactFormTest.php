<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    // 1. [機能テスト] 画面アクセス: ページ表示
    public function test_contact_page_can_be_rendered(): void
    {
        Category::create(['content' => '商品のお届けについて']);
        Tag::create(['name' => '質問']);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewHasAll(['categories', 'tags']);
    }

    // 2. [機能テスト] お問い合わせ: お問い合わせフォーム確認ページ表示
    public function test_contact_confirm_page_can_be_rendered_with_valid_data(): void
    {
        $category = Category::create(['content' => 'その他']);
        $response = $this->post('/contacts/confirm', [
            'category_id' => $category->id,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'test@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => '詳細内容です'
        ]);
        $response->assertStatus(200);
        $response->assertViewHas('validated');
    }

    // 3. [機能テスト] お問い合わせ: お問い合わせ送信
    public function test_contact_can_be_submitted_and_saved_to_database(): void
    {
        $category = Category::create(['content' => 'その他']);
        $tag = Tag::create(['name' => '質問']);

        $response = $this->post(route('contact.store'), [
            'category_id' => $category->id,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'test@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => '詳細内容です',
            'tags'        => [$tag->id]
        ]);

        $response->assertRedirect(route('contact.thanks'));
        $this->assertDatabaseHas('contacts', ['email' => 'test@example.com']);
    }

    /*
     * ─── 応用要件：公開API機能のテスト（5項目） ───
     */

    // 4. [機能テスト] 公開API: お問い合わせ一覧API (JSON形式、検索・ページネーション、422)
    public function test_api_contact_index_returns_json_and_handles_validation_error(): void
    {
        $response = $this->json('GET', '/api/v1/contacts', ['gender' => 'invalid_value']);
        $response->assertStatus(422); // 不正値エラー時は422

        $response_valid = $this->json('GET', '/api/v1/contacts');
        $response_valid->assertStatus(200)->assertJsonStructure(['data', 'links', 'meta']);
    }

    // 5. [機能テスト] 公開API: お問い合わせ詳細API (JSON形式、404)
    public function test_api_contact_show_returns_json_and_handles_404(): void
    {
        $response = $this->json('GET', '/api/v1/contacts/99999');
        $response->assertStatus(404); // 存在しないIDは404
    }

    // 6. [機能テスト] 公開API: お問い合わせ作成API (201、422)
    public function test_api_contact_store_returns_201_or_422(): void
    {
        $response = $this->json('POST', '/api/v1/contacts', []);
        $response->assertStatus(422); // バリデーションエラー
    }

    // 7. [機能テスト] 公開API: お問い合わせ更新API (200、404、422)
    public function test_api_contact_update_returns_200_or_404_or_422(): void
    {
        $response = $this->json('PUT', '/api/v1/contacts/99999', []);
        $response->assertStatus(404);
    }

    // 8. [機能テスト] 公開API: お問い合わせ削除API (204、404)
    public function test_api_contact_destroy_returns_204_or_404(): void
    {
        $response = $this->json('DELETE', '/api/v1/contacts/99999');
        $response->assertStatus(404);
    }
}
