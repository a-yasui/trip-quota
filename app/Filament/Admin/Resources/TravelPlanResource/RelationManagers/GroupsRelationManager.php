<?php

namespace App\Filament\Admin\Resources\TravelPlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';
    
    protected static ?string $modelLabel = 'グループ';
    
    protected static ?string $pluralModelLabel = 'グループ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('グループタイプ')
                    ->options([
                        'CORE' => 'コアグループ',
                        'BRANCH' => '班グループ',
                    ])
                    ->required()
                    ->default('BRANCH')
                    ->disabled(fn ($record) => $record && $record->type === 'CORE'),
                Forms\Components\TextInput::make('name')
                    ->label('グループ名')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('branch_key')
                    ->label('区別用名前')
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('type') === 'BRANCH')
                    ->helperText('他の旅行計画の班グループと連携する際に使用'),
                Forms\Components\Textarea::make('description')
                    ->label('説明')
                    ->rows(3)
                    ->maxLength(1000),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('タイプ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CORE' => 'success',
                        'BRANCH' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'CORE' => 'コア',
                        'BRANCH' => '班',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('グループ名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch_key')
                    ->label('区別用名前')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('メンバー数')
                    ->sortable(),
                Tables\Columns\TextColumn::make('itineraries_count')
                    ->counts('itineraries')
                    ->label('旅程数')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expenses_count')
                    ->counts('expenses')
                    ->label('経費数')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日時')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('グループタイプ')
                    ->options([
                        'CORE' => 'コアグループ',
                        'BRANCH' => '班グループ',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('グループを作成')
                    ->mutateFormDataUsing(function (array $data): array {
                        // コアグループが既に存在する場合は、新規作成時はBRANCHのみ許可
                        $coreGroupExists = \App\Models\Group::where('travel_plan_id', $this->ownerRecord->id)
                            ->where('type', 'CORE')
                            ->exists();
                        
                        if ($coreGroupExists && $data['type'] === 'CORE') {
                            $data['type'] = 'BRANCH';
                        }
                        
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(fn ($record) => $record->type === 'CORE'),
                Tables\Actions\Action::make('manage_members')
                    ->label('メンバー管理')
                    ->icon('heroicon-o-user-group')
                    ->url(fn ($record) => "#")
                    ->tooltip('グループメンバーを管理'),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn ($record) => $record->type === 'CORE')
                    ->requiresConfirmation()
                    ->modalHeading('グループを削除')
                    ->modalDescription('このグループを削除してもよろしいですか？')
                    ->modalSubmitActionLabel('削除'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('選択したグループを削除')
                        ->modalDescription('選択したグループを削除してもよろしいですか？コアグループは削除されません。')
                        ->modalSubmitActionLabel('削除')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->type !== 'CORE') {
                                    $record->delete();
                                }
                            });
                        }),
                ]),
            ]);
    }
}
