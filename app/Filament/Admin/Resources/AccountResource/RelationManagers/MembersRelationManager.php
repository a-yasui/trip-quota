<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

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
                Forms\Components\Select::make('travel_plan_id')
                    ->relationship('travelPlan', 'plan_name')
                    ->label('旅行計画')
                    ->required()
                    ->searchable()
                    ->preload(),
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
                Tables\Columns\TextColumn::make('travelPlan.plan_name')
                    ->label('旅行計画')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('メールアドレス')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('travel_plan')
                    ->relationship('travelPlan', 'plan_name')
                    ->label('旅行計画'),
                Tables\Filters\TernaryFilter::make('is_confirmed')
                    ->label('確認状態'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('メンバーを作成'),
                Tables\Actions\Action::make('associate')
                    ->label('既存メンバーを関連付け')
                    ->modalHeading('既存メンバーをアカウントに関連付け')
                    ->form([
                        Forms\Components\Select::make('member_id')
                            ->label('メンバー')
                            ->options(function () {
                                return \App\Models\Member::whereNull('account_id')
                                    ->orWhere('account_id', '!=', request()->route('record'))
                                    ->with('travelPlan')
                                    ->get()
                                    ->mapWithKeys(function ($member) {
                                        $label = $member->name;
                                        if ($member->travelPlan) {
                                            $label .= ' -- ' . $member->travelPlan->plan_name;
                                        }
                                        return [$member->id => $label];
                                    });
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $member = \App\Models\Member::find($data['member_id']);
                        if ($member) {
                            $member->account_id = $livewire->ownerRecord->id;
                            $member->save();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('dissociate')
                    ->label('関連付け解除')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->modalHeading('メンバーの関連付けを解除')
                    ->modalDescription('このメンバーとアカウントの関連付けを解除してもよろしいですか？')
                    ->modalSubmitActionLabel('解除')
                    ->action(function ($record) {
                        $record->account_id = null;
                        $record->save();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_dissociate')
                        ->label('関連付けを解除')
                        ->requiresConfirmation()
                        ->modalHeading('選択したメンバーの関連付けを解除')
                        ->modalDescription('選択したメンバーとアカウントの関連付けを解除してもよろしいですか？')
                        ->modalSubmitActionLabel('解除')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->account_id = null;
                                $record->save();
                            });
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
