<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('檢視')
                ->icon('heroicon-o-eye'),

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

        return "編輯使用者：{$record->name}";
    }

    /**
     * Get the breadcrumb for this page.
     */
    public function getBreadcrumb(): string
    {
        return '編輯';
    }

    /**
     * Redirect to the list page after updating the record.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Mutate form data before filling the form.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 移除密碼欄位，避免顯示 hash
        unset($data['password_hash']);

        return $data;
    }

    /**
     * Get the notification title.
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return '使用者更新成功';
    }
}
