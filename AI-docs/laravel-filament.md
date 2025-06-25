# Filament
管理画面を作成するパッケージ filament に関するメモです。

## install
```shell
> composer require filament/filament:"^3.3" -W
> php artisan filament:install --panels
```

基本的には設定やテンプレートは操作するので、publishしておく。
```shell
> php artisan vendor:publish --tag=filament-config
> php artisan vendor:publish --tag=filament-actions-translations
> php artisan vendor:publish --tag=filament-forms-translations
> php artisan vendor:publish --tag=filament-infolists-translations
> php artisan vendor:publish --tag=filament-notifications-translations
> php artisan vendor:publish --tag=filament-tables-translations
> php artisan vendor:publish --tag=filament-translations
```

## Upgrade
`composer update ` をする度に `php artisan filament:upgrade` も実行しなければならない。

## 習慣
filament はキャッシュをする。Filament が使うクラスを追加・削除した時は `php artisan filament:optimize` を実行する事。

## 管理者ユーザの作成
管理者ユーザは User Model を拡張して使用する。この例では特定ドメインかつEmailの確認が取れている場合のみ管理画面が使用できる。プロジェクトによっては、この限りでは無い。
```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    // ...

    public function canAccessPanel(Panel $panel): bool
    {
        // ユーザが管理者と判断できる条件分岐
        return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
    }
}
```
# Filament のリソース
Filament で使う物は `app/Filament` 以下に存在する。例外は存在し、User Model は、中に Trait や Interface を実装する必要がある。

## モデルからリソースの作成
Filamentはモデルから管理画面へアクセスするため各種情報へアクセスする Resource クラスが必要になる。それはテンプレートを作成する事ができ、専用のコマンドがあるので、それを使用する。
```shell
> php artisan make:filament-resource <Model Name> --view
```
### タブ表示
一覧画面で種類別けとして使うためにTabを実装する。
```php
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

public function getTabs(): array
{
    return [
        'all' => Tab::make(),
        'active' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
        'inactive' => Tab::make()
            ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
    ];
}
public function getDefaultActiveTab(): string | int | null
{
    return 'active';
}
```

### リソースで一覧表示させるために使うテーブルのカラムとフィルター
これは作成したFilament Resourceクラスの中で実装する。
```php
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            // ...
        ])
        ->filters([
            Tables\Filters\Filter::make('verified')
                ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
            // ...
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}
```

### リソースで操作するレコードのフォーム作成
```php
use Filament\Forms;
use Filament\Forms\Form;

// Filament が作成するformエリアの情報
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('email')->email()->required(),
            // ...
        ]);
}
```