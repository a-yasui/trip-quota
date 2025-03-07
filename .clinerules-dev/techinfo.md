# 技術スタック

使用しているソフトウェアは下記の通りです。

- PHP8.3
- Laravel 11

## プロジェクト

このプロジェクトはユーザがログインしPDFを入稿するシステムです。

## ドメイン

それぞれの機能をドメインとする。ドメインの namespace は `TripQuota\` で始まり、以下に機能名が続く。

ドメインの機能を使うには、そのドメインにある `<ドメイン名>Service` クラスを使うのみである。

そのドメインは、その機能内で完結しており、**他の機能にあるクラスを使ってはならない**。ただし、`<ドメイン名>Service` クラスは除く。

### ドメインモデル

- namespace `App\Model` のクラスを使う時は、 `TripQuota\~~\Model\` 以下に継承したクラスを置き、それを利用する
- テストは `tests/<domain>/` 以下に配置する。
    - テストに使う Factory は `App\Model` の物を使う。
- ドメインの Service クラスを外部から使う時、引数および返り値は全て `TripQuota\~~\Repository\` 以下に配置したインターフェースを実装した物を使用する。
    - ドメインの Service クラスのメソッドに使う引数が Eloquent Model の時は、`App\Mapping\<Domain>\` 以下に Mapping クラスを用意して、それを使う。

#### Bisection クラス例

`App\Model\User` と `App\Http\Request\UserUpdateRequest` を `TripQuota\Account\AccountService::update(UserRepository $user, UpdateDataRepository $data): UserRepository` に渡すとする。

##### UserRepository の Mapping クラス

```
<?php
namespace App\Mapping\Account;
class UserMapping implements \TripQuota\Account\Repository\UserRepository {}
```

## テストデータとmock

Laravel Eloquent は一度 Repository といわれる抽象型に扱われ、Service は Repository でやり取りをする。DummyというRepositoryの最低限の実装をした実態を使いテストをする。

# ディレクトリ構造とドメインの関係

- app にはLaravelが作成するPHPプログラムのみ保持する。
- 機能ごとの役割は `TripQuote` に機能に相応する名前のサブディレクトリを作成し、その中にある `<ドメイン名>Service` クラスで実装をする。
- Controller 内は Service クラスを使うのみである。

# フロント

- フロントは Blade および VueJS3 で作成する。
- CSS Framework は Tailwind を使う。

# PHP Coding

## トランザクションの扱い

- DB トランザクションは `DB::transaction(function(){ /* 追加,変更,削除の処理 */ } )` を実行する。
- `DB::beginTransaction(); ... DB::commmit();` は使用しない。
