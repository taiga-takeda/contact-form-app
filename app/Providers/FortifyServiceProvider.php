<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // 1. ログイン画面のビューを設定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 2. 管理者登録画面のビューを設定
        Fortify::registerView(function () {
            return view('auth.register');
        });
        config(['fortify.username' => 'email']);
        // 🔴【追加】Fortifyのログイン失敗メッセージを裏側で完全に日本語に書き換えます
        $this->app->afterResolving(LoginRequest::class, function ($request) {
            $request->setValidator($this->app['validator']->make(
                $request->all(),
                ['email' => 'required|string', 'password' => 'required|string'],
                [
                    'email.required' => 'メールアドレスを入力してください',
                    'password.required' => 'パスワードを入力してください',
                ]
            ));
        });

        // 🔴【追加】ログイン認証自体が失敗したときのエラーメッセージを強制上書き
        config(['fortify.username' => 'email']);
        Validator::replacer('apps', function () {
            return 'ログイン情報が登録されていません';
        });

        // 既存のビュー設定（そのまま残してください）
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        // 🔴【最も確実な強制上書き】Fortifyの内部ロジックを上書きして日本語でエラーを返します
        Fortify::authenticateUsing(function ($request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            // 認証に失敗した場合、提供された画面が待ち受けている 'email' キーに対して
            // 仕様書通りの日本語エラーを直接投げつけます
            throw ValidationException::withMessages([
                'email' => ['ログイン情報が登録されていません'],
            ]);
        });
    }
}
