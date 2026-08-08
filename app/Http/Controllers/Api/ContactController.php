<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Http\Resources\ContactResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource. (お問い合わせ一覧取得API)
     */
    public function index(Request $request)
    {
        // 1. 不正値（genderに不正な値が入っているなど）を検証するバリデーション
        $validator = Validator::make($request->all(), [
            'gender' => 'nullable|in:1,2,3',
            'category_id' => 'nullable|integer',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        // 不正値が検出された場合は「422 Unprocessable Entity」を返す
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Unprocessable Entity',
                'messages' => $validator->errors()
            ], 422);
        }

        // 2. Eager Loading (with) を指定
        $query = Contact::with(['category', 'tags']);

        // 3. 一般の管理画面と同様に、各検索フィルタ（絞り込み）を適用
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('first_name', 'like', '%' . $keyword . '%')
                  ->orWhere('last_name', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 4. 1ページの表示件数を決定
        $perPage = $request->input('per_page', 15);
        $contacts = $query->latest()->paginate($perPage);

        // 5. データをリソース化して返却
        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json(['error' => 'Unprocessable Entity'], 422);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(['error' => 'Not Found'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json(['error' => 'Not Found'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json(['error' => 'Not Found'], 404);
    }
}
