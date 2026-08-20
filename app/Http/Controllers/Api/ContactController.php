<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * AP01: お問い合わせ一覧取得
     */
    public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'gender' => 'nullable|in:1,2,3',
            'category_id' => 'nullable|integer',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'バリデーションエラー', 'errors' => $validator->errors()], 422);
        }

        $query = Contact::with(['category', 'tags']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', '%'.$keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
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

        $perPage = $request->input('per_page', 20);
        $contacts = $query->latest()->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * AP03: お問い合わせ登録
     */
    public function store(StoreContactRequest $request)
    {
        // 1. バリデーション済みの安全なデータを取得
        $validated = $request->validated();

        // 2. データベースに保存
        $contact = Contact::create($validated);

        // 3. $contact->tags()->attach($tagIds) でタグ紐付け
        if ($request->has('tag_ids')) {
            $contact->tags()->attach($request->tag_ids);
        }

        // 4. load(['category', 'tags']) して ContactResource でラップし 201 で返却
        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    /**
     * AP02: お問い合わせ詳細取得
     */
    public function show(string $id)
    {
        try {
            // ルートモデルバインディング（に準拠したID検索）と 404カスタムレスポンス
            $contact = Contact::with(['category', 'tags'])->findOrFail($id);

            return new ContactResource($contact);
        } catch (ModelNotFoundException $e) {
            // 存在しない場合は指定のJSONと404を返却
            return response()->json(['error' => 'お問い合わせが見つかりませんでした。'], 404);
        }
    }

    /**
     * AP04: お問い合わせ更新
     */
    public function update(Request $request, string $id)
    {
        try {
            $contact = Contact::findOrFail($id);

            $updateRequest = new UpdateContactRequest;
            $validator = Validator::make(
                $request->all(),
                $updateRequest->rules()
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'バリデーションエラー',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // 3. 入力チェックを通過した安全なデータで更新処理を実行
            $contact->update($validator->validated());

            // 4. $contact->tags()->sync($tagIds) でタグ同期
            if ($request->has('tag_ids')) {
                $contact->tags()->sync($request->tag_ids);
            } else {
                $contact->tags()->sync([]);
            }

            // 5. loadしてContactResourceでラップし 200 で返却
            $contact->load(['category', 'tags']);

            return new ContactResource($contact);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'お問い合わせが見つかりませんでした。'], 404);
        }
    }

    /**
     * AP05: お問い合わせ削除
     */
    public function destroy(string $id)
    {
        try {
            $contact = Contact::findOrFail($id);

            // contact_tag は外部キーの cascade により自動削除
            $contact->delete();

            // 削除成功時は空ボディ（null）と 204 を返却
            return response()->json(null, 204);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'お問い合わせが見つかりませんでした。'], 404);
        }
    }
}
