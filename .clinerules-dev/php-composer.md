# php composer の扱い

## 初期作成

- `composer create-project laravel/laravel .`
- もし `TripQuota` ディレクトリが無い時は作成をする。
- `composer.json` の `autoload.psr-4` に `"TripQuota\\": "TripQuota/"` が無い時は追加をする。

## 必要パッケージのインストール

`composer require <package_name>`

## 必要パッケージのインストール（開発環境のみ）

`composer require --dev <package_name>`

## パッケージの更新

`composer up --dev -W`

