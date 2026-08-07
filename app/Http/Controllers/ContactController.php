<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class ContactController extends Controller
{
    // PG01: 入力画面を表示
    public function index()
    {
        $categories = Category::all();
        $tags = \App\Models\Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    // PG02: 確認画面を表示
    public function confirm(Request $request)
    {
        $tel1 = $request->input('tel1') ?? $request->input('tell1');
        $tel2 = $request->input('tel2') ?? $request->input('tell2');
        $tel3 = $request->input('tel3') ?? $request->input('tell3');
        if ($tel1 && $tel2 && $tel3) {
            $request->merge(['tel' => $tel1 . $tel2 . $tel3]);
        }
        // 1. バリデーションを実行し、チェックを通過した安全なデータだけを「$validated」という変数に代入します
        $validated = $request->validate([
            'category_id' => 'required',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'gender'      => 'required',
            'email'       => 'required|string|email|max:255',
            'tel'         => 'required|numeric|digits_between:10,11',
            'address'     => 'required|string|max:255',
            'building'    => 'nullable|string|max:255',
            'detail'      => 'required|string|max:120',
        ], [
            'required' => ':attributeは必須項目です。',
        ]);

        // 2. カテゴリの文字列を取得
        $category = Category::find($request->category_id);

        // 3. 画面が求めている変数名「validated」に合わせてデータを渡して表示させます
        return view('contact.confirm', compact('validated', 'category'));
    }


    // PG03: サンクス画面を表示
    public function thanks()
    {
        return view('contact.thanks');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'gender'      => 'required',
            'email'       => 'required|string|email|max:255',
            'tel'         => 'required|numeric|digits_between:10,11',
            'address'     => 'required|string|max:255',
            'building'    => 'nullable|string|max:255',
            'detail'      => 'required|string|max:120',
        ]);

        // 2. データベース（contactsテーブル）に保存
        $contact = \App\Models\Contact::create($validated);

        // 3.
        if ($request->has('tags')) {
            $contact->tags()->sync($request->tags);
        }

        // 4. 二重送信を防ぐため、リダイレクトでサンクスページへ飛ばします
        return redirect()->route('contact.thanks');
    }
}
