<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('建立使用者')
                ->icon('heroicon-o-plus'),
        ];
    }

    /**
     * Get the page title.
     */
    public function getTitle(): string
    {
        return '使用者管理';
    }

    /**
     * Get the breadcrumb for this page.
     */
    public function getBreadcrumb(): string
    {
        return '列表';
    }
}
