<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        // 全タグを取得してBladeに渡します
        $tags = Tag::all();

        $query = Contact::with(['category', 'tags']);

        // a. 名前・メールの「部分一致」検索
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', '%'.$keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
            });
        }

        // 性別検索のバグ修正
        if ($request->filled('gender') && $request->gender != '0') {
            $query->where('gender', $request->gender);
        }

        // c. カテゴリの検索（完全一致）
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // d. 作成日の検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $contacts = $query->latest()->paginate(7);
        $contacts->appends($request->all());

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    // お問い合わせデータの削除処理
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return redirect()->route('admin.index')->with('success', 'お問い合わせデータを削除しました。');
    }

    // お問い合わせ詳細画面
    public function show(Request $request, $id)
    {
        $contact = Contact::with(['category', 'tags'])->findOrFail($id);

        return view('admin.show', compact('contact'));
    }

    // タグの新規保存（追加）処理
    public function tagStore(StoreTagRequest $request)
    {

        Tag::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.index')->with('success', '新しいタグを追加しました。');
    }

    // タグの削除処理
    public function tagDestroy($id)
    {
        $tag = Tag::findOrFail($id);

        // 中間テーブル（contact_tag）の関連レコードも自動で安全に削除
        $tag->contacts()->detach();
        $tag->delete();

        return redirect()->route('admin.index')->with('success', 'タグを削除しました。');
    }

    // 🔴【追加】詳細リスト33行目：タグ編集ページに遷移し、現在のデータをフォームに表示する
    public function tagEdit($id)
    {
        $tag = Tag::findOrFail($id);

        return view('admin.tags.edit', compact('tag')); // 'admin.tags.edit' に修正
    }

    // 🔴【追加】詳細リスト35行目：タグ名を変更して「更新」を押すと、変更が反映される
    public function tagUpdate(UpdateTagRequest $request, $id)
    {

        $tag = Tag::findOrFail($id);
        $tag->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.index')->with('success', 'タグ名を更新しました。');
    }

    // CSVエクスポート（完全実装済み）
    public function export(Request $request)
    {
        $query = Contact::with('category');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', '%'.$keyword.'%')
                    ->orWhere('last_name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
            });
        }

        if ($request->filled('gender') && $request->gender != '0') {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $contacts = $query->latest()->get();

        $callback = function () use ($contacts) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM追加でExcel文字化け防止
            fputcsv($file, ['ID', 'お名前', '性別', 'メールアドレス', 'お問い合わせの種類', 'ご意見', '登録日時']);

            foreach ($contacts as $contact) {
                $gender = '';
                if ($contact->gender == 1) {
                    $gender = '男性';
                } elseif ($contact->gender == 2) {
                    $gender = '女性';
                } elseif ($contact->gender == 3) {
                    $gender = 'その他';
                }

                fputcsv($file, [
                    $contact->id,
                    $contact->first_name.' '.$contact->last_name,
                    $gender,
                    $contact->email,
                    $contact->category ? $contact->category->content : '未選択',
                    $contact->detail,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        $filename = 'contacts_'.date('YmdHis').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
