<?php

namespace App\Filament\Admin\Resources\TravelPlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';
    
    protected static ?string $modelLabel = 'メンバー';
    
    protected static ?string $pluralModelLabel = 'メンバー';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名前')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('メールアドレス')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->label('ユーザー')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('account_id')
                    ->label('アカウント')
                    ->options(function ($record) {
                        if ($record && $record->user_id) {
                            return \App\Models\Account::where('user_id', $record->user_id)
                                ->pluck('account_name', 'id');
                        }
                        return [];
                    })
                    ->searchable()
                    ->nullable()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set) => $state ? null : null),
                Forms\Components\Toggle::make('is_confirmed')
                    ->label('確認済み')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('メールアドレス')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('ユーザー')
                    ->searchable()
                    ->sortable()
                    ->placeholder('未登録'),
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('アカウント')
                    ->searchable()
                    ->sortable()
                    ->placeholder('未設定'),
                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('確認済み')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日時')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_confirmed')
                    ->label('確認状態'),
                Tables\Filters\TernaryFilter::make('has_user')
                    ->label('ユーザー登録')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('user_id'),
                        false: fn (Builder $query) => $query->whereNull('user_id'),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\TernaryFilter::make('has_account')
                    ->label('アカウント設定')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('account_id'),
                        false: fn (Builder $query) => $query->whereNull('account_id'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('メンバーを追加'),
            ])
            ->actions([
                Tables\Actions\EditAction::make('hoge'),
                Tables\Actions\Action::make('toggle_confirmation')
                    ->label(fn ($record) => $record->is_confirmed ? '未確認に変更' : '確認済みに変更')
                    ->icon(fn ($record) => $record->is_confirmed ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->is_confirmed = !$record->is_confirmed;
                        $record->save();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('メンバーを削除')
                    ->modalDescription('このメンバーを削除してもよろしいですか？')
                    ->modalSubmitActionLabel('削除'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_confirm')
                        ->label('確認済みにする')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->is_confirmed = true;
                                $record->save();
                            });
                        }),
                    Tables\Actions\BulkAction::make('bulk_unconfirm')
                        ->label('未確認にする')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->is_confirmed = false;
                                $record->save();
                            });
                        }),
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('選択したメンバーを削除')
                        ->modalDescription('選択したメンバーを削除してもよろしいですか？')
                        ->modalSubmitActionLabel('削除'),
                ]),
            ]);
    }
}
