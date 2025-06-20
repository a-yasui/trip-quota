# Laravel Framework spec

Laravel を使ったアプリケーションの作り方のガイドラインです。

## Command

目的のコマンドを実行する時は次に従う。

- Cast を作る時: `php artisan make:cast <Cast Name>`
- Command を作る時: `php artisan make:command <Command Class Name`
- Enum を作る時: `php artisan make:enum <Enum Name>`
- Event を作る時: `php artisan make:event <Event Class Name>`
- factory を作る時: `php artisan generate:factory <Eloquent Class Name>`
- Job を作る時: `php artisan make:job --phpunit <Job Class Name>`
- Job Middleware を作る時: `php artisan make:job-middleware`
- Listener Class を作る時: `php artisan make:listener -e <Event Class Name> --queued --phpunit <Listener Class Name>`
- Mail Class を作る時: `php artisan make:mail --markdown --phpunit <Mail Class Name>`
- Middleware を作る時: `php artisan make:middleware <Middleware Class Name>`
- Migration を作る時: `php artisan make:migration {--table=<table>} <良い感じの名前>`
- Eloquent Model を作る時: `php artisan make:model <Eloquent Class Name>`
- Notification Class を作る時: `php artisan make:notification <Notification Class name>`
- Policy Class を作る時: `php artisan make:policy --model=<model> --guard=<guard> <Policy Class Name>`
- Provider Class を作る時: `php artisan make:provider <Class Name>`
- Request を作る時: `php artisan make:request <Request Class Name>`
- Rule を作る時: `php artisan make:rule <Rule Name>`
- Scope を作る時: `php artisan make:scope <scope name>`
- Seeder を作る時: `php artisan make:seeder <seeder name>`
- UnitTest 等を作る時: `php artisan make:test --phpunit <UnitName>`
- test を実行する: `php artisan test`

## Database

- Database で Enum を使う時は、クラスの Enum を作成してから Migration を作り、それをマイグレーションに入れる事。
- Enum に値の増減がある時は、その都度 Migration を作成する事。
- JSON 型を使用してはいけない。JSON の値をDBに入れる時は、 `text` 型のカラムを用意しそこに挿入する。挿入するデータは、JSONの値を
  base64化してからDBに入れるようにし、使う時は `json_decode(base64_decode($value), true)` のように使う事。なおこれは Cast
  クラスを作成して、そのクラス内で処理し、Eloquentはその Cast を使うようにする。

## Cast

- JSON にするデータを扱う時は、readonly のカスタムクラスを作成し、型を必ず付ける事。

### JSON を PHP クラスで扱う

- `app/Models/JSON`ディレクトリ内にカスタムクラスを作成する。Cast クラスはこれを使用する。
- クラスは基本 readonly である。
- Constractor は `public function __construct(readonly int $prop1,...)` といった感じで、プロパティごとに readonly 属性を付ける。
- DB に挿入するために `public function toBase64JSON(): string` を実装する事
- DB から復元するため `public static function fromBase64JSON(string $data): self` を実装する事

## Eloquent

- JSON の値を保存する時は `base64_encode(json_encode($falue))` を必ず通す事。なお、これは Cast クラスで操作する事。
- 新しいクラスや、カラムの変更、キャストの修正等がある時は、そのテーブルに対応するEloquentに対して
  `php artisan ide-helper:model -R <eloquent class path>` を実行する。プロパティにカラム情報を書くのが必須である。

## Exception Class

- 新規に作成する Exception クラスは、必ず  `App\Exception\Exception` を継承、ないしはそのクラスを継承したクラスを使う事。
- `\Exception`を継承しているクラスは `App\Exception\Exception` のみにする事
- `\Exception`を直接利用してはいけない。必ずベースクラス `App\Exception\Exception` を継承したクラスを使う。

## Controller

- 極力 Controller 内は Service に対しての入出力や、イベント発生、メール送信（Mail::send）や、Request/Response に注力し、try..catch
  の例外処理はしない。
- 例外処理は `bootstrap/app.php` にある withExceptions で取り扱う。
    - 全ての例外を扱うのは難しくなるので `App\Exception\Exception` の子クラスで振る舞いを切り替える形にする
    - 例外発生は、ログに warning ないしは error として、Contextにはログインしていれば user_id と url を保存する。

## Route

- `Route::resource` は使わない事。基本的に `Route::get`/`Route::post` を使う。
- `Route::model` は積極的に使うようにする。

## Validation

入力値の確認で、標準のValidation Ruleがない時は、 Rule を作成し、それを使用する。作成したRuleは必ず UnitTest
を実行して期待する挙動になるか確証をもたせる。

## Blade

- `@example` と表示させいたい時、`@{{ $user->name }}` とするのは間違いで、`{{ '@' . $user->name }}` とする。
  `@{{ $user->name }}` は blade escape が走り、`{{ $user->name }}` と表示される為。
- 基本 `views/layouts/master.blade.php` を使う事。例外はあり、ログインする前のページ（トップページ、ログインページ、パスワードを忘れた時のリマインだーページ）はこれに準じない。

## Test

- 極力書くようにする。
- Validation rule は特に、成功テスト（値が存在している、想定している値のみ）、失敗テスト（値が存在してない、プロパティがない、日付なのにランダム文字列が来た等）、境界地テスト（時分秒単位での指定など）、null Check を必ず書く事。
- メール送信やイベント発生といった fake でとらえる物は `Mail::fake();` 等で assertSent 等チェックをする事
