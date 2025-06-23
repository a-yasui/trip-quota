<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TravelPlanResource\Pages;
use App\Filament\Admin\Resources\TravelPlanResource\RelationManagers;
use App\Models\TravelPlan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TravelPlanResource extends Resource
{
    protected static ?string $model = TravelPlan::class;

    protected static ?string $navigationIcon = 'simpleline-plane';
    
    protected static ?string $modelLabel = '旅行計画';
    
    protected static ?string $pluralModelLabel = '旅行計画';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\TextInput::make('plan_name')
                            ->label('旅行計画名')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('uuid')
                            ->label('UUID')
                            ->disabled()
                            ->dehydrated(false)
                            ->hiddenOn('create'),
                        Forms\Components\Select::make('creator_user_id')
                            ->label('作成者')
                            ->relationship('creator', 'email')
                            ->required()
                            ->searchable()
                            ->preload()
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
                        Forms\Components\Select::make('owner_user_id')
                            ->label('所有者（削除権限）')
                            ->relationship('owner', 'email')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('旅行日程')
                    ->schema([
                        Forms\Components\DatePicker::make('departure_date')
                            ->label('出発日')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y年m月d日'),
                        Forms\Components\DatePicker::make('return_date')
                            ->label('帰宅日')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('Y年m月d日')
                            ->afterOrEqual('departure_date'),
                        Forms\Components\Select::make('timezone')
                            ->label('タイムゾーン')
                            ->options([
                                'Asia/Tokyo' => 'Asia/Tokyo (JST)',
                                'Asia/Seoul' => 'Asia/Seoul (KST)',
                                'Asia/Shanghai' => 'Asia/Shanghai (CST)',
                                'Asia/Hong_Kong' => 'Asia/Hong_Kong (HKT)',
                                'America/New_York' => 'America/New_York (EST/EDT)',
                                'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)',
                                'Europe/London' => 'Europe/London (GMT/BST)',
                                'Europe/Paris' => 'Europe/Paris (CET/CEST)',
                            ])
                            ->required()
                            ->default('Asia/Tokyo'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('アクティブ')
                            ->default(true),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('詳細情報')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('説明')
                            ->rows(3)
                            ->maxLength(1000),
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
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plan_name')
                    ->label('旅行計画名')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.email')
                    ->label('作成者')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner.email')
                    ->label('所有者')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('出発日')
                    ->date('Y年m月d日')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_date')
                    ->label('帰宅日')
                    ->date('Y年m月d日')
                    ->sortable()
                    ->placeholder('未定'),
                Tables\Columns\TextColumn::make('timezone')
                    ->label('タイムゾーン')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('アクティブ')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('メンバー数')
                    ->sortable()
                    ->alignment(Alignment::End),
                Tables\Columns\TextColumn::make('groups_count')
                    ->counts('groups')
                    ->label('グループ数')
                    ->sortable()
                    ->alignment(Alignment::End),
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
                Tables\Filters\SelectFilter::make('creator')
                    ->relationship('creator', 'email')
                    ->label('作成者')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('アクティブ状態'),
                Tables\Filters\Filter::make('has_return_date')
                    ->label('帰宅日設定済み')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('return_date')),
                Tables\Filters\Filter::make('departure_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('開始日'),
                        Forms\Components\DatePicker::make('until')
                            ->label('終了日'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('departure_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('departure_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('旅行計画を削除')
                    ->modalDescription('この旅行計画を削除してもよろしいですか？この操作は取り消せません。')
                    ->modalSubmitActionLabel('削除'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('選択した旅行計画を削除')
                        ->modalDescription('選択した旅行計画を削除してもよろしいですか？この操作は取り消せません。')
                        ->modalSubmitActionLabel('削除'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MembersRelationManager::class,
            RelationManagers\GroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTravelPlans::route('/'),
            'create' => Pages\CreateTravelPlan::route('/create'),
            'view' => Pages\ViewTravelPlan::route('/{record}'),
            'edit' => Pages\EditTravelPlan::route('/{record}/edit'),
        ];
    }
}
