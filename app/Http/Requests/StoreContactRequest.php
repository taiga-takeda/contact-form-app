<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|integer|in:1,2,3', //  integer|in:1,2,3 に修正
            'email' => 'required|string|email|max:255',
            'tel' => 'required|string|regex:/^[0-9]{10,11}$/', //  string|regex に修正
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'category_id' => 'required|integer|exists:categories,id', //  integer|exists に修正
            'detail' => 'required|string|max:120',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attributeは必須項目です。',
        ];
    }
}
