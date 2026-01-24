<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Password;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('編輯')
                ->icon('heroicon-o-pencil'),

            Actions\Action::make('reset_password')
                ->label('重設密碼')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('重設密碼')
                ->modalDescription(fn ($record) => "確定要發送密碼重設郵件給 {$record->email} 嗎？")
                ->action(function ($record) {
                    // 發送密碼重設郵件
                    $status = Password::sendResetLink(['email' => $record->email]);

                    if ($status === Password::RESET_LINK_SENT) {
                        Notification::make()
                            ->success()
                            ->title('密碼重設郵件已發送')
                            ->body("已發送密碼重設郵件至 {$record->email}")
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('發送失敗')
                            ->body('無法發送密碼重設郵件，請稍後再試')
                            ->send();
                    }
                }),

            Actions\Action::make('change_role')
                ->label('變更角色')
                ->icon('heroicon-o-user-circle')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('role')
                        ->label('新角色')
                        ->options([
                            User::ROLE_USER => '一般使用者',
                            User::ROLE_SALESPERSON => '業務員',
                            User::ROLE_ADMIN => '管理員',
                        ])
                        ->required()
                        ->native(false)
                        ->default(fn ($record) => $record->role),

                    Forms\Components\Select::make('salesperson_status')
                        ->label('業務員狀態')
                        ->options([
                            User::STATUS_PENDING => '待審核',
                            User::STATUS_APPROVED => '已批准',
                            User::STATUS_REJECTED => '已拒絕',
                        ])
                        ->native(false)
                        ->visible(fn (Forms\Get $get) => $get('role') === User::ROLE_SALESPERSON)
                        ->helperText('變更為業務員時需要設定狀態'),
                ])
                ->action(function ($record, array $data) {
                    $oldRole = $record->role;
                    $newRole = $data['role'];

                    $updateData = ['role' => $newRole];

                    // 如果變更為業務員，設定業務員狀態
                    if ($newRole === User::ROLE_SALESPERSON) {
                        $updateData['salesperson_status'] = $data['salesperson_status'] ?? User::STATUS_PENDING;
                        if (! $record->salesperson_applied_at) {
                            $updateData['salesperson_applied_at'] = now();
                        }
                    }

                    // 如果從業務員變更為其他角色，清除業務員狀態
                    if ($oldRole === User::ROLE_SALESPERSON && $newRole !== User::ROLE_SALESPERSON) {
                        $updateData['salesperson_status'] = null;
                    }

                    $record->update($updateData);

                    Notification::make()
                        ->success()
                        ->title('角色變更成功')
                        ->body("已將 {$record->name} 的角色從 ".
                            match ($oldRole) {
                                User::ROLE_ADMIN => '管理員',
                                User::ROLE_SALESPERSON => '業務員',
                                User::ROLE_USER => '使用者',
                                default => $oldRole,
                            }.' 變更為 '.
                            match ($newRole) {
                                User::ROLE_ADMIN => '管理員',
                                User::ROLE_SALESPERSON => '業務員',
                                User::ROLE_USER => '使用者',
                                default => $newRole,
                            })
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->label('刪除')
                ->icon('heroicon-o-trash'),
        ];
    }

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        /** @var \App\Models\User $record */
        $record = $this->record;

        return "檢視使用者：{$record->name}";
    }

    /**
     * Get the breadcrumb for this page.
     */
    public function getBreadcrumb(): string
    {
        return '檢視';
    }
}
