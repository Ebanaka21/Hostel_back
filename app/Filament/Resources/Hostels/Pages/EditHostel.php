<?php

namespace App\Filament\Resources\Hostels\Pages;

use App\Filament\Resources\Hostels\HostelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHostel extends EditRecord
{
    protected static string $resource = HostelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
