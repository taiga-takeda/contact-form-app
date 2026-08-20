<?php

namespace Tests\Unit;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 90行目対策：タグ更新時、自身の名前維持は可能だが他との重複は拒否されることを検証
     */
    public function test_tag_update_validation_allows_own_name_but_rejects_duplicate(): void
    {
        // 1. テスト用のダミーデータを2つ作成
        $tag1 = Tag::create(['name' => '既存タグA']);
        $tag2 = Tag::create(['name' => '既存タグB']);

        // 2. 自分自身の名前のまま更新する場合（バリデーションを通過するはず）
        $rules1 = [
            'name' => 'required|string|max:50|unique:tags,name,'.$tag1->id,
        ];
        $validator1 = Validator::make(['name' => '既存タグA'], $rules1);
        $this->assertFalse($validator1->fails());

        // 3. 他のタグの名前と重複して更新しようとする場合（弾かれるはず）
        $validator2 = Validator::make(['name' => '既存タグB'], $rules1);
        $this->assertTrue($validator2->fails());
    }
}
