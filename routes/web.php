<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
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
| 認証・管理者向けルート（基本要件）
|--------------------------------------------------------------------------
*/

// 管理者登録画面の表示と登録処理
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// ログイン画面の表示とログイン処理
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ログアウト処理
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 管理画面ルート（基本要件）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // 管理画面トップ（一覧・検索）
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    // 追加：お問い合わせ詳細表示
    Route::get('/admin/contacts/{id}', [AdminController::class, 'show'])->name('admin.show');

    // お問い合わせデータの削除処理
    Route::delete('/admin/contacts/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // 追加：タグ管理画面のCRUD（基本要件）
    Route::get('/admin/tags', [AdminController::class, 'tagIndex'])->name('admin.tags.index');

    // 追加：CSVダウンロード（応用要件）
    Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');
});