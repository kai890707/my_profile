<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        return '建立使用者';
    }

    /**
     * Get the breadcrumb for this page.
     */
    public function getBreadcrumb(): string
    {
        return '建立';
    }

    /**
     * Redirect to the list page after creating the record.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Mutate form data before creating the record.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 如果沒有設定 username，使用 email 前綴
        if (empty($data['username']) && isset($data['email'])) {
            /** @var string $email */
            $email = $data['email'];
            $data['username'] = explode('@', $email)[0];
        }

        // 設定預設 status
        if (! isset($data['status'])) {
            $data['status'] = 'active';
        }

        return $data;
    }

    /**
     * Get the notification title.
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return '使用者建立成功';
    }
}
