<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        // テスト用の共通カテゴリを作成
        $this->category = Category::create(['content' => 'APIテストカテゴリ']);
    }

    /**
     * 🔴 103, 105行目：お問い合わせ一覧API、JSON取得、検索、ページネーション、不正値の拒否
     */
    public function test_api_index_with_filters_pagination_and_validation(): void
    {
        Contact::factory()->count(5)->create(['gender' => 1, 'category_id' => $this->category->id]);

        // 正常な取得と検索の検証
        $response = $this->getJson('/api/v1/contacts?gender=1&per_page=5');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);

        // 不正な性別値(0)を送信したときに弾かれるバリデーション検証
        $badResponse = $this->getJson('/api/v1/contacts?gender=0');
        $badResponse->assertStatus(422);
    }

    /**
     * 🔴 104, 107行目：お問い合わせ作成API、全必須項目・タグの受付、不正値の拒否、201レスポンス
     */
    public function test_api_store_creates_contact_with_tags_and_validates(): void
    {
        $tag = Tag::create(['name' => 'APIタグ']);

        $data = [
            'category_id' => $this->category->id,
            'first_name' => 'API',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'api@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'detail' => 'APIからのテスト投稿です。',
            'tag_ids' => [$tag->id],
        ];

        // 201 Created レンスポンスとDB保存の検証
        $response = $this->postJson('/api/v1/contacts', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', ['email' => 'api@example.com']);

        // 不正な値を送信したときのバリデーション拒否を検証
        $invalidData = $data;
        $invalidData['email'] = 'invalid-email-format';
        $this->postJson('/api/v1/contacts', $invalidData)->assertStatus(422);
    }

    /**
     * 🔴 106行目：お問い合わせ詳細API、詳細取得と404エラーの検証
     */
    public function test_api_show_returns_contact_details_or_404(): void
    {
        $contact = Contact::factory()->create(['category_id' => $this->category->id]);

        // 正常に200 OKと詳細データが返ってくる検証
        $response = $this->getJson("/api/v1/contacts/{$contact->id}");
        $response->assertStatus(200);

        // 存在しないIDのときに404エラーが返ってくる検証
        $this->getJson('/api/v1/contacts/99999')->assertStatus(404);
    }

    /**
     * 🔴 108行目：お問い合わせ更新API、200レスポンス、更新反映、404エラーの検証
     */
    public function test_api_update_modifies_contact_or_returns_404(): void
    {
        // 💡 ファクトリのデフォルト設定を完全に無視させ、確実に動的なID（$this->category->id）をデータベースに強制注入します
        $contact = Contact::factory()->create([
            'category_id' => $this->category->id,
            'first_name' => '古い名前',
        ]);

        $updateData = [
            'category_id' => $this->category->id,
            'first_name' => '新しい名前',
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ];

        // 200 OKとデータ更新の反映を検証
        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);
        $response->assertStatus(200);

        // データベースに「新しい名前」と「正しいカテゴリID」で保存されているか厳密にチェック
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '新しい名前',
            'category_id' => $this->category->id,
        ]);

        // 存在しないIDのときに404エラーになる検証
        $this->putJson('/api/v1/contacts/99999', $updateData)->assertStatus(404);
    }

    /**
     * 🔴 109行目：お問い合わせ削除API、204レスポンス、レコード削除、404エラーの検証
     */
    public function test_api_destroy_deletes_contact_or_returns_404(): void
    {
        $contact = Contact::factory()->create(['category_id' => $this->category->id]);

        // 204 No Content とDBから削除されたかを検証
        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);

        // 存在しないIDのときに404エラーになる検証
        $this->deleteJson('/api/v1/contacts/99999')->assertStatus(404);
    }
}
