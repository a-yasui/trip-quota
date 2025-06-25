<?php

namespace App\Filament\Admin\Resources\GroupResource\RelationManagers;

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
    
    protected static ?string $title = 'メンバー';
    
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
                Forms\Components\Toggle::make('is_confirmed')
                    ->label('確認済み')
                    ->inline()
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('メールアドレス')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('ユーザーアカウント')
                    ->searchable()
                    ->sortable()
                    ->placeholder('未登録'),
                Tables\Columns\IconColumn::make('is_confirmed')
                    ->label('確認済み')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('追加日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_confirmed')
                    ->label('確認状態')
                    ->boolean()
                    ->trueLabel('確認済みのみ')
                    ->falseLabel('未確認のみ')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('has_user')
                    ->label('ユーザー登録')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('user_id'),
                        false: fn (Builder $query) => $query->whereNull('user_id'),
                    )
                    ->trueLabel('登録済み')
                    ->falseLabel('未登録')
                    ->nullable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        return $query->where('travel_plan_id', $this->ownerRecord->travel_plan_id);
                    })
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}