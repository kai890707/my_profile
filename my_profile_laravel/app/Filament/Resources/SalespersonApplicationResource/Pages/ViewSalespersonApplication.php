<?php

declare(strict_types=1);

namespace App\Filament\Resources\SalespersonApplicationResource\Pages;

use App\Filament\Resources\SalespersonApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSalespersonApplication extends ViewRecord
{
    protected static string $resource = SalespersonApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
