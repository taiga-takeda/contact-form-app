<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Category;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    // 要件：未認証ユーザーは管理画面（/admin）へアクセスすると /login にリダイレクトされること
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    // 要件：認証された管理者のみが管理画面へアクセスできること
    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }

    // 要件：管理画面でデータ削除ができること
    public function test_admin_can_delete_contact(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['content' => 'その他']);
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name'  => '削除',
            'last_name'   => '太郎',
            'gender'      => 1,
            'email'       => 'delete@example.com',
            'tel'         => '09012345678',
            'address'     => '住所',
            'detail'      => '本文'
        ]);

        // ログインした状態で削除ルーティングへDELETEリクエストを送る
        $response = $this->actingAs($user)->delete(route('admin.destroy', $contact->id));

        $response->assertRedirect(route('admin.index'));
        // データベースから消えているか確認
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
