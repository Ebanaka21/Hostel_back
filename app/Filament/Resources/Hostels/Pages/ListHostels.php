<?php

namespace App\Filament\Resources\Hostels\Pages;

use App\Filament\Resources\Hostels\HostelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHostels extends ListRecords
{
    protected static string $resource = HostelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
