<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GroupResource\Pages;
use App\Filament\Admin\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use App\Models\TravelPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $modelLabel = 'グループ';
    
    protected static ?string $pluralModelLabel = 'グループ';
    
    protected static ?string $navigationLabel = 'グループ';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\Select::make('travel_plan_id')
                            ->label('旅行計画')
                            ->relationship('travelPlan', 'plan_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('plan_name')
                                    ->label('計画名')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('departure_date')
                                    ->label('出発日')
                                    ->required(),
                                Forms\Components\DatePicker::make('return_date')
                                    ->label('帰国日'),
                            ]),
                        Forms\Components\Select::make('type')
                            ->label('グループタイプ')
                            ->options([
                                'CORE' => 'コアグループ',
                                'BRANCH' => '班グループ',
                            ])
                            ->required()
                            ->native(false)
                            ->disabledOn('edit'),
                        Forms\Components\TextInput::make('name')
                            ->label('グループ名')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('branch_key')
                            ->label('ブランチキー')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('type') === 'BRANCH')
                            ->helperText('班グループの識別子（自動生成されます）'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('追加情報')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('説明')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('travelPlan.plan_name')
                    ->label('旅行計画')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('タイプ')
                    ->colors([
                        'primary' => 'CORE',
                        'success' => 'BRANCH',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'CORE' => 'コア',
                        'BRANCH' => '班',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('グループ名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch_key')
                    ->label('ブランチキー')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('members_count')
                    ->label('メンバー数')
                    ->counts('members')
                    ->suffix('人')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新日時')
                    ->dateTime('Y/m/d H:i')
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
                Tables\Filters\SelectFilter::make('travel_plan')
                    ->relationship('travelPlan', 'plan_name')
                    ->label('旅行計画')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Group $record) {
                        // COREグループは削除不可
                        if ($record->type === 'CORE') {
                            throw new \Exception('コアグループは削除できません。');
                        }
                        // メンバーがいる場合は削除不可
                        if ($record->members()->exists()) {
                            throw new \Exception('メンバーが存在するグループは削除できません。');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->type === 'CORE') {
                                    throw new \Exception('コアグループは削除できません。');
                                }
                                if ($record->members()->exists()) {
                                    throw new \Exception('メンバーが存在するグループは削除できません。');
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'view' => Pages\ViewGroup::route('/{record}'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['travelPlan', 'members']);
    }
}
