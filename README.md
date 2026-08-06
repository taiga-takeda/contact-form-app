# プロジェクト名
COACHTECH お問い合わせフォーム開発プロジェクト（確認テスト）

## 概要
本プロジェクトは、一般ユーザーからのお問い合わせを受け付けるフォーム機能、および管理者が送信されたお問い合わせデータを閲覧・検索できる管理システムを統合したWebアプリケーションです。

## 使用技術
- **フレームワーク**: Laravel 10.x
- **データベース**: MySQL 8.0
- **Webサーバー**: Nginx
- **開発環境**: Docker / Laravel Sail (WSL 2環境にて動作確認済み)
- **フロントエンド**: Bladeテンプレート / Vite (CSS, JS)

## 開発環境URL
- **一般ユーザー向け（入力画面）**: http://localhost
- **管理者向け管理画面**: http://localhost/admin
- **管理者登録画面**: http://localhost/register
- **ログイン画面**: http://localhost/login
- **データベース管理（phpMyAdmin）**: http://localhost:8080

## 環境構築手順
本アプリケーションをローカル環境で起動するための手順。

1. **リポジトリのクローン**
   ```bash
   git clone git@github.com:taiga-takeda/-.git
   cd Preparedblade-ConfirmationTest-ContactForm
   ```

2. **環境設定ファイルの準備**
   `.env.example` をコピーして `.env` を作成します。
   ```bash
   cp .env.example .env
   ```

3. **Dockerコンテナの起動（Laravel Sail）**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **依存パッケージのインストール**
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ```

5. **アプリケーションキーの生成**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. **データベースのマイグレーションと初期データ投入**
   ```bash
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```

7. **フロントエンド資産のビルド（Viteの起動）**
   ```bash
   ./vendor/bin/sail npm run dev
   ```

## ER図
![ER図](images/er-diagram.png)

## APIエンドポイント一覧
今回の基本要件に基づいて実装されたWebアプリケーションのエンドポイント一覧です。

| メソッド | パス | 概要 | 画面名 / 機能 |
| :--- | :--- | :--- | :--- |
| **GET** | `/` | お問い合わせ入力画面の表示 | PG01: 入力ページ |
| **POST** | `/contacts/confirm` | 入力値のバリデーションと確認画面表示 | PG02: 確認ページ |
| **POST** | `/contacts` | データベースへの保存処理（完了後リダイレクト） | お問い合わせ送信処理 |
| **GET** | `/thanks` | 送信完了画面の表示 | PG03: サンクスページ |
| **GET** | `/register` | 管理者アカウント新規登録画面の表示 | 管理者登録ページ |
| **POST** | `/register` | 管理者アカウントの新規登録処理 | 管理者登録処理 |
| **GET** | `/login` | 管理者ログイン画面の表示 | ログインページ |
| **POST** | `/login` | 管理者ログイン認証処理 | ログイン処理 |
| **POST** | `/logout` | 管理者ログアウト処理（セッション破棄） | ログアウト処理 |
| **GET** | `/admin` | お問い合わせ一覧の表示および検索処理（※要ログイン） | 管理画面 |

## 作成者
武田大河
