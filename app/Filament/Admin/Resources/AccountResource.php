<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AccountResource\Pages;
use App\Filament\Admin\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $modelLabel = 'アカウント';
    
    protected static ?string $pluralModelLabel = 'アカウント';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('アカウント情報')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->label('ユーザー')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('email')
                                    ->label('メールアドレス')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->label('パスワード')
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('account_name')
                            ->label('アカウント名')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->regex('/^[a-zA-Z][\w\-_]{3,}$/')
                            ->helperText('英字で始まり、英数字、アンダースコア、ハイフンのみ使用可能（4文字以上）')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('display_name')
                            ->label('表示名')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('thumbnail_url')
                            ->label('サムネイル画像URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('bio')
                            ->label('自己紹介')
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('メールアドレス')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('アカウント名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('表示名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('サムネイル')
                    ->circular(),
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('メンバー数')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日時')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新日時')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'email')
                    ->label('ユーザー')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('アカウントを削除')
                    ->modalDescription('このアカウントを削除してもよろしいですか？この操作は取り消せません。')
                    ->modalSubmitActionLabel('削除'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('選択したアカウントを削除')
                        ->modalDescription('選択したアカウントを削除してもよろしいですか？この操作は取り消せません。')
                        ->modalSubmitActionLabel('削除'),
                ]),
            ])
            ->recordUrl(
                fn (Account $record): string => Pages\EditAccount::getUrl(['record' => $record])
            );
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'view' => Pages\ViewAccount::route('/{record}'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
