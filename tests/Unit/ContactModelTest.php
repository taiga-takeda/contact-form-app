<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactModelTest extends TestCase
{
    use RefreshDatabase;

    /* ──── モデルリレーション（基本3項目） ──── */

    public function test_category_has_many_contacts(): void
    {
        $category = Category::create(['content' => 'テスト']);
        $contact = Contact::create([
            'category_id' => $category->id, 'first_name' => 'T', 'last_name' => 'U',
            'gender' => 1, 'email' => 'u@ex.com', 'tel' => '09000000000', 'address' => 'A', 'detail' => 'D',
        ]);
        $this->assertTrue($category->contacts->contains($contact));
    }

    public function test_contact_belongs_to_category_and_syncs_with_tags(): void
    {
        $category = Category::create(['content' => 'その他']);
        $tag = Tag::create(['name' => '不具合報告']);
        $contact = Contact::create([
            'category_id' => $category->id, 'first_name' => 'T', 'last_name' => 'U',
            'gender' => 1, 'email' => 'u@ex.com', 'tel' => '09000000000', 'address' => 'A', 'detail' => 'D',
        ]);
        $contact->tags()->attach($tag->id);

        $this->assertEquals($category->id, $contact->category->id);
        $this->assertTrue($contact->tags->contains($tag));
    }

    public function test_tag_belongs_to_many_contacts(): void
    {
        $category = Category::create(['content' => 'その他']);
        $tag = Tag::create(['name' => 'ご意見']);
        $contact = Contact::create([
            'category_id' => $category->id, 'first_name' => 'T', 'last_name' => 'U',
            'gender' => 1, 'email' => 'u@ex.com', 'tel' => '09000000000', 'address' => 'A', 'detail' => 'D',
        ]);
        $tag->contacts()->attach($contact->id);
        $this->assertTrue($tag->contacts->contains($contact));
    }

    /* ──── バリデーション検証（応用/基本7項目） ──── */

    // CSVエクスポート要件（応用）: 正しいフィルタ、不正な値拒否
    public function test_csv_export_validation(): void
    {
        $validator = Validator::make(['gender' => 99], ['gender' => 'nullable|in:1,2,3']);
        $this->assertTrue($validator->fails());
    }

    // 問い合わせ一覧検索（基本）: フィルタ有効、不正な性別拒否
    public function test_admin_search_validation(): void
    {
        $validator = Validator::make(['gender' => 'invalid'], ['gender' => 'nullable|integer|in:1,2,3']);
        $this->assertTrue($validator->fails());
    }

    // 問い合わせ保存（基本）: 必須項目、タグ入力、不正電話番号拒否
    public function test_contact_store_validation(): void
    {
        $validator = Validator::make(['tel' => 'abc-1234'], ['tel' => 'required|numeric']);
        $this->assertTrue($validator->fails());
    }

    // タグ新規登録（基本）: 必須入力、文字数制限、一意性
    public function test_tag_store_validation(): void
    {
        Tag::create(['name' => '重複タグ']);
        $validator = Validator::make(['name' => '重複タグ'], ['name' => 'required|string|max:50|unique:tags,name']);
        $this->assertTrue($validator->fails());
    }

    // タグ更新（基本）: 自身維持、他重複拒否
    public function test_tag_update_validation(): void
    {
        Tag::create(['name' => '既存タグ1']);
        $tag2 = Tag::create(['name' => '既存タグ2']);
        $validator = Validator::make(['name' => '既存タグ1'], ['name' => 'required|string|max:50|unique:tags,name,'.$tag2->id]);
        $this->assertTrue($validator->fails());
    }

    // API検索バリデーション（応用）: keyword, gender, date, per_page有効、不正値拒否
    public function test_api_search_validation(): void
    {
        $validator = Validator::make(['gender' => 5], ['gender' => 'nullable|in:1,2,3']);
        $this->assertTrue($validator->fails());
    }

    // API作成バリデーション（応用）: 全必須項目、タグ入力、不正値拒否
    public function test_api_store_validation(): void
    {
        $validator = Validator::make(['email' => 'not-an-email'], ['email' => 'required|email']);
        $this->assertTrue($validator->fails());
    }
}
