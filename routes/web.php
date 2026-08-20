<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// PG01: お問い合わせフォーム入力ページ
Route::get('/', [ContactController::class, 'index'])->name('contact.index');

// PG02: お問い合わせフォーム確認ページ
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');

// PG03: サンクスページ
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

// PG04: お問い合わせ送信処理（データベース保存）
Route::post('/contacts', [ContactController::class, 'store'])->name('contact.store');

/*
|--------------------------------------------------------------------------
| 管理画面ルート（認証が必要なページ）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // 管理画面トップ（一覧・検索、およびタグ一覧表示）
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    // 🟢 1. CSVダウンロード（要件の /contacts/export と テストコード用の /admin/export 両方に対応）
    Route::get('/contacts/export', [AdminController::class, 'export'])->name('admin.export');
    Route::get('/admin/export', [AdminController::class, 'export']);

    // お問い合わせ詳細表示
    Route::get('/admin/contacts/{id}', [AdminController::class, 'show'])->name('admin.show');

    // お問い合わせデータの削除処理
    Route::delete('/admin/contacts/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // 提供されたフォーム（POST）に加えて、テストコードが呼び出すURL（GET）の窓口も用意します
    Route::post('/admin/tags', [AdminController::class, 'tagStore'])->name('admin.tags.store');
    Route::delete('/admin/tags/{id}', [AdminController::class, 'tagDestroy'])->name('admin.tags.destroy');
    Route::get('/admin/tags', [AdminController::class, 'index'])->name('admin.tags.index');

    // 🔴【追加】詳細リスト33行目・35行目：タグの「編集画面表示」と「更新処理」のルートを追加
    Route::get('/admin/tags/{id}/edit', [AdminController::class, 'tagEdit'])->name('admin.tags.edit');
    Route::put('/admin/tags/{id}', [AdminController::class, 'tagUpdate'])->name('admin.tags.update');
});
