<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SalespersonApplicationResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalespersonApplicationResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = '業務員申請';

    protected static ?string $navigationGroup = '審核管理';

    protected static ?int $navigationSort = 1;

    /**
     * Navigation Badge (顯示待審核數量).
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('salesperson_status', User::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Navigation Badge Color.
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = (int) static::getNavigationBadge();

        return match (true) {
            $count > 10 => 'danger',
            $count > 5 => 'warning',
            default => 'success',
        };
    }

    /**
     * Eloquent Query (只顯示業務員申請).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', User::ROLE_SALESPERSON)
            ->whereIn('salesperson_status', [User::STATUS_PENDING, User::STATUS_APPROVED, User::STATUS_REJECTED])
            ->with('salespersonProfile');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('salespersonProfile.full_name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('salespersonProfile.phone')
                    ->label('電話')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('salespersonProfile.company.name')
                    ->label('公司')
                    ->default('(自營業者)')
                    ->searchable(),

                Tables\Columns\TextColumn::make('salesperson_applied_at')
                    ->label('申請時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->default('N/A'),

                Tables\Columns\TextColumn::make('waiting_time')
                    ->label('等待時間')
                    ->getStateUsing(function ($record) {
                        if (! $record->salesperson_applied_at) {
                            return 'N/A';
                        }
                        $hours = now()->diffInHours($record->salesperson_applied_at);
                        if ($hours < 24) {
                            return '<24h';
                        }
                        if ($hours < 72) {
                            return '1-3天';
                        }
                        if ($hours < 168) {
                            return '3-7天';
                        }

                        return '7天+';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '<24h' => 'success',
                        '1-3天' => 'warning',
                        '3-7天' => 'warning',
                        '7天+' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('salesperson_status')
                    ->label('狀態')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '待審核',
                        'approved' => '已批准',
                        'rejected' => '已拒絕',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('salesperson_status')
                    ->label('狀態')
                    ->options([
                        'pending' => '待審核',
                        'approved' => '已批准',
                        'rejected' => '已拒絕',
                    ]),

                Tables\Filters\Filter::make('waiting_time')
                    ->label('等待時間')
                    ->form([
                        Forms\Components\Select::make('waiting')
                            ->options([
                                '24h' => '24小時內',
                                '1-3' => '1-3天',
                                '3-7' => '3-7天',
                                '7+' => '7天以上',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['waiting'])) {
                            return $query;
                        }

                        return match ($data['waiting']) {
                            '24h' => $query->where('salesperson_applied_at', '>=', now()->subHours(24)),
                            '1-3' => $query->whereBetween('salesperson_applied_at', [now()->subDays(3), now()->subHours(24)]),
                            '3-7' => $query->whereBetween('salesperson_applied_at', [now()->subDays(7), now()->subDays(3)]),
                            '7+' => $query->where('salesperson_applied_at', '<', now()->subDays(7)),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('has_company')
                    ->label('有公司')
                    ->query(fn (Builder $query): Builder => $query->whereHas('salespersonProfile', fn ($q) => $q->whereNotNull('company_id')
                    )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('批准')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('批准業務員申請')
                    ->modalDescription(fn ($record) => "確定要批准 {$record->salespersonProfile->full_name} 的申請嗎？")
                    ->action(function ($record) {
                        $record->approveSalesperson();
                        Notification::make()
                            ->success()
                            ->title('已批准')
                            ->body("{$record->salespersonProfile->full_name} 的申請已批准")
                            ->send();
                    })
                    ->visible(fn ($record) => $record->salesperson_status === User::STATUS_PENDING),

                Tables\Actions\Action::make('reject')
                    ->label('拒絕')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('拒絕原因')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\Select::make('can_reapply_days')
                            ->label('可重新申請天數')
                            ->options([
                                7 => '7天後',
                                14 => '14天後',
                                30 => '30天後',
                                90 => '90天後',
                            ])
                            ->default(30)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->rejectSalesperson(
                            $data['rejection_reason'],
                            $data['can_reapply_days']
                        );
                        Notification::make()
                            ->warning()
                            ->title('已拒絕')
                            ->body("{$record->salespersonProfile->full_name} 的申請已拒絕")
                            ->send();
                    })
                    ->visible(fn ($record) => $record->salesperson_status === User::STATUS_PENDING),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_approve')
                    ->label('批量批准')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $approved = 0;
                        foreach ($records as $record) {
                            if ($record->salesperson_status === User::STATUS_PENDING) {
                                $record->approveSalesperson();
                                $approved++;
                            }
                        }
                        Notification::make()
                            ->success()
                            ->title('批量批准完成')
                            ->body("已批准 {$approved} 筆申請")
                            ->send();
                    }),
            ])
            ->defaultSort('salesperson_applied_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('基本資料')
                    ->schema([
                        Components\TextEntry::make('salespersonProfile.full_name')->label('姓名'),
                        Components\TextEntry::make('email')->label('Email'),
                        Components\TextEntry::make('salespersonProfile.phone')->label('電話'),
                        Components\TextEntry::make('salesperson_applied_at')
                            ->label('申請時間')
                            ->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(2),

                Components\Section::make('業務員資訊')
                    ->schema([
                        Components\TextEntry::make('salespersonProfile.bio')
                            ->label('個人簡介')
                            ->default('未填寫'),
                        Components\TextEntry::make('salespersonProfile.specialties')
                            ->label('專長領域')
                            ->default('未填寫'),
                        Components\TextEntry::make('salespersonProfile.service_regions')
                            ->label('服務地區')
                            ->listWithLineBreaks()
                            ->default('未填寫'),
                        Components\TextEntry::make('salespersonProfile.company.name')
                            ->label('公司')
                            ->default('自營業者'),
                    ])
                    ->columns(2),

                Components\Section::make('審核狀態')
                    ->schema([
                        Components\TextEntry::make('salesperson_status')
                            ->label('狀態')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => '待審核',
                                'approved' => '已批准',
                                'rejected' => '已拒絕',
                                default => $state,
                            }),
                        Components\TextEntry::make('salesperson_approved_at')
                            ->label('審核時間')
                            ->dateTime('Y-m-d H:i')
                            ->default('N/A'),
                        Components\TextEntry::make('rejection_reason')
                            ->label('拒絕原因')
                            ->default('N/A')
                            ->visible(fn ($record) => $record->salesperson_status === User::STATUS_REJECTED),
                        Components\TextEntry::make('can_reapply_at')
                            ->label('可重新申請時間')
                            ->dateTime('Y-m-d')
                            ->default('N/A')
                            ->visible(fn ($record) => $record->salesperson_status === User::STATUS_REJECTED),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalespersonApplications::route('/'),
            'view' => Pages\ViewSalespersonApplication::route('/{record}'),
        ];
    }
}
