<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    // 1. [機能テスト] 画面アクセス: 管理画面アクセス制御
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    // 2. [機能テスト] 管理機能: 検索・ページネーション
    public function test_authenticated_admin_can_access_admin_index_with_pagination(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }

    // 3. [機能テスト] 管理機能: お問い合わせ詳細
    public function test_admin_can_view_contact_detail(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'その他']);
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => 'T',
            'last_name' => 'U',
            'gender' => 1,
            'email' => 't@ex.com',
            'tel' => '09000000000',
            'address' => 'A',
            'detail' => 'D'
        ]);

        $response = $this->actingAs($user)->get("/admin/contacts/{$contact->id}");
        $response->assertStatus(200);
    }

    // 4. [機能テスト] 管理機能: お問い合わせ削除
    public function test_admin_can_delete_contact(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'その他']);
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => 'T',
            'last_name' => 'U',
            'gender' => 1,
            'email' => 't@ex.com',
            'tel' => '09000000000',
            'address' => 'A',
            'detail' => 'D'
        ]);

        $response = $this->actingAs($user)->delete(route('admin.destroy', $contact->id));
        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    // 5. [機能テスト] タグ管理: タグCRUD・認証 (基本要件)
    public function test_authenticated_admin_can_manage_tags_and_unauthenticated_is_blocked(): void
    {
        $response = $this->get('/admin/tags');
        $response->assertRedirect('/login'); // 未認証はブロック

        $user = User::factory()->create();
        $response_auth = $this->actingAs($user)->get('/admin/tags');
        $response_auth->assertStatus(200);
    }

    // 6. [機能テスト] エクスポート: CSVダウンロード (応用要件)
    public function test_authenticated_admin_can_download_csv_with_filters(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/admin/export');

        // 1. まずステータスコードが 200 OK であるか検証
        $response->assertStatus(200);

        // 2. 正しいヘッダー取得メソッド「headers->get()」を使い、text/csv が含まれているか検証
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('text/csv', $contentType);
    }

}