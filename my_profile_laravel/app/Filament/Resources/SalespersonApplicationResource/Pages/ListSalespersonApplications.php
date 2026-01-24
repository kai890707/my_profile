<?php

declare(strict_types=1);

namespace App\Filament\Resources\SalespersonApplicationResource\Pages;

use App\Filament\Resources\SalespersonApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListSalespersonApplications extends ListRecords
{
    protected static string $resource = SalespersonApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
