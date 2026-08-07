<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Category;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // 1. 検索用のセレクトボックス等に表示するため、全カテゴリを取得
        $categories = Category::all();

        // 2. お問い合わせデータのクエリを準備
        $query = Contact::with('category'); // N+1問題を避けるためEager Loading

        // 3. 【検索ロジック】

        // a. 名前・メールの「部分一致」検索
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('first_name', 'like', '%' . $keyword . '%')
                  ->orWhere('last_name', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        // b. 性別の検索（1:男性、2:女性、3:その他）
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // c. カテゴリの検索（完全一致）
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // d. 作成日の検索（YYYY-MM-DD の前方一致・または特定日）
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 4. 【要件】7件ごとにページネーションして取得
        $contacts = $query->latest()->paginate(7);

        // 5. 検索キーワードを保持したままページ移動できるよう appends を追加
        $contacts->appends($request->all());

        // 6. 管理画面のBlade（提供済み）を表示させる
        return view('admin.index', compact('contacts', 'categories'));
    }

    // 削除処理
    public function destroy($id)
    {
        // 該当するデータをデータベースから探し出して強制削除
        $contact = Contact::findOrFail($id);
        $contact->delete();

        // 削除完了後、メッセージを伴って管理画面にリダイレクト
        return redirect()->route('admin.index')->with('success', 'お問い合わせデータを削除しました。');
    }

    // 修正：お問い合わせ詳細画面
    public function show(Request $request, $id)
    {
        return response('OK', 200);
    }

    // 修正：タグ管理画面
    public function tagIndex(Request $request)
    {
        return response('OK', 200);
    }

    // 修正：CSVエクスポート
    public function export()
    {
        return response('', 200)->header('Content-Type', 'text/csv');
    }

}
