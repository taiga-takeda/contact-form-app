<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    // PG01: 入力画面を表示
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    // PG02: 確認画面を表示
    public function confirm(StoreContactRequest $request)
    {
        $tel1 = $request->input('tel1') ?? $request->input('tell1');
        $tel2 = $request->input('tel2') ?? $request->input('tell2');
        $tel3 = $request->input('tel3') ?? $request->input('tell3');
        if ($tel1 && $tel2 && $tel3) {
            $request->merge(['tel' => $tel1.$tel2.$tel3]);
        }

        $validated = $request->validated();
        $category = Category::find($request->category_id);
        $tags = Tag::whereIn('id', $request->input('tag_ids', []))->get();

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }

    // PG03: サンクス画面を表示
    public function thanks()
    {
        return view('contact.thanks');
    }

    // 🔴引数を StoreContactRequest $request に変更
    public function store(StoreContactRequest $request)
    {
        // 手動の $request->validate は不要になったので丸ごと削除
        // フォームリクエストで検証済みのデータを安全に取得します
        $validated = $request->validated();

        // 2. データベース（contactsテーブル）に保存
        $contact = Contact::create($validated);

        // 3. 【修正】確認画面から送られてくる正しいキー名 'tag_ids' を使って中間テーブルに保存
        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->input('tag_ids'));
        }

        // 4. 二重送信を防ぐため、リダイレクトでサンクスページへ飛ばします
        return redirect()->route('contact.thanks');
    }
}
