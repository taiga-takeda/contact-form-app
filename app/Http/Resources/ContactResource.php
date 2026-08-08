<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'gender'       => $this->gender,
            'email'        => $this->email,
            'tel'          => $this->tel,
            'address'      => $this->address,
            'building'     => $this->building,
            'detail'       => $this->detail,

            // 1対多のリレーション先（カテゴリ）の情報を整形
            'category' => [
                'id'      => $this->category->id ?? null,
                'content' => $this->category->content ?? null,
            ],

            // 多対多のリレーション先（タグ一覧）をループ処理で配列化
            'tags' => $this->tags->map(function ($tag) {
                return [
                    'id'   => $tag->id,
                    'name' => $tag->name,
                ];
            })->toArray(),

            // 日時をISO 8601形式に変換
            'created_at'   => $this->created_at->toIso8601String(),
            'updated_at'   => $this->updated_at->toIso8601String(),
        ];
    }
}
