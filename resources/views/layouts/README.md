# TripQuota マスターレイアウト

このディレクトリには、TripQuotaアプリケーションのマスターレイアウトが含まれています。このレイアウトは、アプリケーション全体で一貫したユーザーインターフェースを提供するために使用されます。

## 基本構造

マスターレイアウト（`app.blade.php`）は以下の主要なセクションで構成されています：

1. **ヘッダー**: ロゴ、通知アイコン、ユーザープロフィールドロップダウンを含む
2. **サイドバー**: 主要なナビゲーションリンクを含む
3. **メインコンテンツ**: 各ページの固有のコンテンツを表示するエリア
4. **フッター**: 著作権情報とその他のリンクを含む

## 使用方法

### 基本的な使用方法

新しいページを作成する際は、以下のように`app.blade.php`レイアウトを継承します：

```blade
@extends('layouts.app')

@section('title', 'ページタイトル')

@section('header', 'ページヘッダー')

@section('content')
    <!-- ページの内容 -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            ページコンテンツがここに入ります
        </div>
    </div>
@endsection
```

### 利用可能なセクション

マスターレイアウトでは、以下のセクションをオーバーライドできます：

- **title**: ブラウザのタイトルバーに表示されるタイトル
- **header**: ページの主要な見出し
- **actions**: ページヘッダーの右側に表示されるアクション（ボタンなど）
- **content**: ページのメインコンテンツ
- **styles**: 追加のCSSスタイル（`@push('styles')`を使用）
- **scripts**: 追加のJavaScriptコード（`@push('scripts')`を使用）

### アクションボタンの追加

ページにアクションボタンを追加するには、`actions`セクションを使用します：

```blade
@section('actions')
    <a href="{{ route('travel-plans.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-lime-600 hover:bg-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">
        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        新規作成
    </a>
@endsection
```

### スタイルとスクリプトの追加

ページ固有のスタイルやスクリプトを追加するには、`push`ディレクティブを使用します：

```blade
@push('styles')
    <style>
        /* ページ固有のスタイル */
    </style>
@endpush

@push('scripts')
    <script>
        // ページ固有のJavaScript
    </script>
@endpush
```

## レスポンシブデザイン

マスターレイアウトは完全にレスポンシブで、以下の画面サイズに対応しています：

- **デスクトップ**: フルレイアウト表示
- **タブレット**: サイドバーが折りたたみ可能
- **モバイル**: ハンバーガーメニューとオーバーレイサイドバー

## カラースキーム

アプリケーションの基調色は `#cbf542`（明るいライムグリーン）です。このカラーはTailwind CSSの`lime-500`に近いです。

## コンポーネント

マスターレイアウトには以下の主要なコンポーネントが含まれています：

- **アラートメッセージ**: 成功や失敗のメッセージを表示
- **ナビゲーションリンク**: アクティブ状態のスタイリングを含む
- **ドロップダウンメニュー**: ユーザープロフィールなどのオプション用
- **モバイルメニュー**: 小さな画面用のレスポンシブナビゲーション

## 例

サンプルページとして以下が実装されています：

1. **ダッシュボード**: `resources/views/dashboard.blade.php`
2. **旅行計画一覧**: `resources/views/travel-plans/index.blade.php`

これらのページを参考にして、新しいページを作成することができます。
