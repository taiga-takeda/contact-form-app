<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 1. 管理者登録画面を表示
    public function showRegister()
    {
        return view('auth.register');
    }

    // 2. 管理者登録処理（データベース保存）
    public function register(Request $request)
    {
        // バリデーションルール（仕様書通り：名前必須、メール必須・一意、パスワード8文字以上）
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'required' => ':attributeは必須入力です。',
            'email'    => 'メールアドレスはメール形式で入力してください。',
            'unique'   => 'このメールアドレスは既に登録されています。',
            'min'      => 'パスワードは8文字以上で入力してください。',
        ]);

        // データベース（usersテーブル）に新の管理者を保存
        // パスワードは必ず Hash::make() で暗号化（ハッシュ化）して保存します
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 登録完了後、自動的にログイン状態にする
        Auth::login($user);

        // ログイン後は管理画面（/admin）へ移動
        return redirect('/admin');
    }

    // 3. ログイン画面を表示
    public function showLogin()
    {
        return view('auth.login');
    }

    // 4. ログイン処理
    public function login(Request $request)
    {
        // 入力チェック
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'required' => ':attributeを入力してください。',
        ]);

        // ログイン試行
        if (Auth::attempt($credentials)) {
            // セッションの再生成（セキュリティ対策）
            $request->session()->regenerate();

            // 認証成功：管理画面へリダイレクト
            return redirect('/admin');
        }

        // 認証失敗：エラーメッセージを伴ってログイン画面に戻す
        return back()->withErrors([
            'login_error' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    // 5. ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout();

        // セッションのクリア
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログイン画面へ戻す
        return redirect('/login');
    }
}
