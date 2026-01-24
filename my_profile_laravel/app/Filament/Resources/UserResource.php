<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = '使用者管理';

    protected static ?string $navigationGroup = '系統管理';

    protected static ?int $navigationSort = 10;

    /**
     * Global search (搜尋使用者名稱和 Email).
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本資訊')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('姓名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('username')
                            ->label('使用者名稱')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password_hash')
                            ->label('密碼')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->helperText('最少 8 個字元。留空則不變更密碼。'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('角色與權限')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('角色')
                            ->options([
                                User::ROLE_USER => '一般使用者',
                                User::ROLE_SALESPERSON => '業務員',
                                User::ROLE_ADMIN => '管理員',
                            ])
                            ->required()
                            ->native(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // 當角色變更為非業務員時，清除業務員狀態
                                if ($state !== User::ROLE_SALESPERSON) {
                                    $set('salesperson_status', null);
                                }
                            }),

                        Forms\Components\Select::make('salesperson_status')
                            ->label('業務員狀態')
                            ->options([
                                User::STATUS_PENDING => '待審核',
                                User::STATUS_APPROVED => '已批准',
                                User::STATUS_REJECTED => '已拒絕',
                            ])
                            ->native(false)
                            ->visible(fn (Forms\Get $get): bool => $get('role') === User::ROLE_SALESPERSON)
                            ->helperText('只有角色為業務員時才需要設定'),

                        Forms\Components\Toggle::make('is_paid_member')
                            ->label('付費會員')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('帳號狀態')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('狀態')
                            ->options([
                                'active' => '啟用',
                                'inactive' => '停用',
                            ])
                            ->required()
                            ->native(false)
                            ->default('active'),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email 驗證時間')
                            ->native(false)
                            ->helperText('設定此時間表示 Email 已驗證'),
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
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn ($record) => $record->username),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('角色')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_SALESPERSON => 'success',
                        User::ROLE_USER => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => '管理員',
                        User::ROLE_SALESPERSON => '業務員',
                        User::ROLE_USER => '使用者',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('salesperson_status')
                    ->label('業務員狀態')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        User::STATUS_PENDING => 'warning',
                        User::STATUS_APPROVED => 'success',
                        User::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        User::STATUS_PENDING => '待審核',
                        User::STATUS_APPROVED => '已批准',
                        User::STATUS_REJECTED => '已拒絕',
                        default => 'N/A',
                    })
                    ->visible(fn () => request()->input('tableFilters.role.value') === User::ROLE_SALESPERSON),

                Tables\Columns\TextColumn::make('status')
                    ->label('帳號狀態')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => '啟用',
                        'inactive' => '停用',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('註冊時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Email 驗證時間')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default('未驗證'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('角色')
                    ->options([
                        User::ROLE_USER => '使用者',
                        User::ROLE_SALESPERSON => '業務員',
                        User::ROLE_ADMIN => '管理員',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('salesperson_status')
                    ->label('業務員狀態')
                    ->options([
                        User::STATUS_PENDING => '待審核',
                        User::STATUS_APPROVED => '已批准',
                        User::STATUS_REJECTED => '已拒絕',
                    ])
                    ->native(false)
                    ->visible(fn () => request()->input('tableFilters.role.value') === User::ROLE_SALESPERSON),

                Tables\Filters\SelectFilter::make('status')
                    ->label('帳號狀態')
                    ->options([
                        'active' => '啟用',
                        'inactive' => '停用',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('created_at')
                    ->label('註冊時間範圍')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('起始日期')
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('結束日期')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email 已驗證')
                    ->nullable()
                    ->trueLabel('已驗證')
                    ->falseLabel('未驗證')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('toggle_status')
                    ->label(fn ($record) => $record->status === 'active' ? '停用' : '啟用')
                    ->icon(fn ($record) => $record->status === 'active' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->status === 'active' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => ($record->status === 'active' ? '停用' : '啟用').'使用者帳號')
                    ->modalDescription(fn ($record) => '確定要'.($record->status === 'active' ? '停用' : '啟用')." {$record->name} 的帳號嗎？")
                    ->action(function ($record) {
                        $newStatus = $record->status === 'active' ? 'inactive' : 'active';
                        $record->update(['status' => $newStatus]);

                        Notification::make()
                            ->success()
                            ->title('操作成功')
                            ->body("{$record->name} 的帳號已".($newStatus === 'active' ? '啟用' : '停用'))
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('刪除'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('刪除選中的使用者'),

                    Tables\Actions\BulkAction::make('bulk_activate')
                        ->label('批量啟用')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $activated = 0;
                            foreach ($records as $record) {
                                if ($record->status !== 'active') {
                                    $record->update(['status' => 'active']);
                                    $activated++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title('批量啟用完成')
                                ->body("已啟用 {$activated} 個使用者帳號")
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_deactivate')
                        ->label('批量停用')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $deactivated = 0;
                            foreach ($records as $record) {
                                if ($record->status !== 'inactive') {
                                    $record->update(['status' => 'inactive']);
                                    $deactivated++;
                                }
                            }
                            Notification::make()
                                ->success()
                                ->title('批量停用完成')
                                ->body("已停用 {$deactivated} 個使用者帳號")
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('基本資訊')
                    ->schema([
                        Components\TextEntry::make('id')->label('ID'),
                        Components\TextEntry::make('name')->label('姓名'),
                        Components\TextEntry::make('username')->label('使用者名稱')->default('未設定'),
                        Components\TextEntry::make('email')->label('Email')->copyable(),
                    ])
                    ->columns(2),

                Components\Section::make('角色與權限')
                    ->schema([
                        Components\TextEntry::make('role')
                            ->label('角色')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                User::ROLE_ADMIN => 'danger',
                                User::ROLE_SALESPERSON => 'success',
                                User::ROLE_USER => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                User::ROLE_ADMIN => '管理員',
                                User::ROLE_SALESPERSON => '業務員',
                                User::ROLE_USER => '使用者',
                                default => $state,
                            }),

                        Components\TextEntry::make('salesperson_status')
                            ->label('業務員狀態')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                User::STATUS_PENDING => 'warning',
                                User::STATUS_APPROVED => 'success',
                                User::STATUS_REJECTED => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                User::STATUS_PENDING => '待審核',
                                User::STATUS_APPROVED => '已批准',
                                User::STATUS_REJECTED => '已拒絕',
                                default => 'N/A',
                            })
                            ->visible(fn ($record) => $record->role === User::ROLE_SALESPERSON),

                        Components\TextEntry::make('status')
                            ->label('帳號狀態')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => '啟用',
                                'inactive' => '停用',
                                default => $state,
                            }),

                        Components\TextEntry::make('is_paid_member')
                            ->label('付費會員')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? '是' : '否')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ])
                    ->columns(2),

                Components\Section::make('業務員詳細資訊')
                    ->schema([
                        Components\TextEntry::make('salesperson_applied_at')
                            ->label('申請時間')
                            ->dateTime('Y-m-d H:i')
                            ->default('N/A'),

                        Components\TextEntry::make('salesperson_approved_at')
                            ->label('審核時間')
                            ->dateTime('Y-m-d H:i')
                            ->default('N/A'),

                        Components\TextEntry::make('rejection_reason')
                            ->label('拒絕原因')
                            ->default('N/A')
                            ->columnSpanFull(),

                        Components\TextEntry::make('can_reapply_at')
                            ->label('可重新申請時間')
                            ->dateTime('Y-m-d')
                            ->default('N/A'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->role === User::ROLE_SALESPERSON),

                Components\Section::make('時間記錄')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->label('註冊時間')
                            ->dateTime('Y-m-d H:i'),

                        Components\TextEntry::make('email_verified_at')
                            ->label('Email 驗證時間')
                            ->dateTime('Y-m-d H:i')
                            ->default('未驗證'),

                        Components\TextEntry::make('updated_at')
                            ->label('最後更新時間')
                            ->dateTime('Y-m-d H:i'),

                        Components\TextEntry::make('deleted_at')
                            ->label('刪除時間')
                            ->dateTime('Y-m-d H:i')
                            ->default('N/A'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
