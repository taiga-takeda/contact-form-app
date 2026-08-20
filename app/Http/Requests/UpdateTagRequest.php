<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 🔴 必ず true に変更
    }

    public function rules(): array
    {
        $id = $this->route('id'); // ルートパラメータからタグIDを取得

        return [
            // 🔴 必須入力、50文字以内、自分以外の重複禁止
            'name' => 'required|string|max:50|unique:tags,name,'.$id,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'タグ名を入力してください。',
            'name.max' => 'タグ名は50文字以内で入力してください。',
            'name.unique' => 'そのタグ名は既に登録されています。',
        ];
    }
}
