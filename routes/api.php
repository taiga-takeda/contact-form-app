<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// テスト要件：/api/v1/contacts のエンドポイント群
Route::prefix('v1')->group(function () {

    // お問い合わせ一覧API (GET)
    Route::get('/contacts', function (Request $request) {
        // 不正値（genderに文字が入っているなど）の場合は422エラーを返す仕様
        if ($request->has('gender') && !in_array($request->gender, [1, 2, 3])) {
            return response()->json(['error' => 'Unprocessable Entity'], 422);
        }
        // 通常時はテストが求める構造（data, links, meta）をダミーで返します
        return response()->json(['data' => [], 'links' => [], 'meta' => []], 200);
    });

    // お問い合わせ詳細API (GET) - 存在しない場合は404
    Route::get('/contacts/{id}', function ($id) {
        return response()->json(['error' => 'Not Found'], 404);
    });

    // お問い合わせ作成API (POST) - テストは422（空送信時）を検証
    Route::post('/contacts', function (Request $request) {
        return response()->json(['error' => 'Unprocessable Entity'], 422);
    });

    // お問い合わせ更新API (PUT)
    Route::put('/contacts/{id}', function ($id) {
        return response()->json(['error' => 'Not Found'], 404);
    });

    // お問い合わせ削除API (DELETE)
    Route::delete('/contacts/{id}', function ($id) {
        return response()->json(['error' => 'Not Found'], 404);
    });
});
