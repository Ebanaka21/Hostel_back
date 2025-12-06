<?php

namespace App\Filament\Resources\Hostels;

use App\Filament\Resources\Hostels\Pages\CreateHostel;
use App\Filament\Resources\Hostels\Pages\EditHostel;
use App\Filament\Resources\Hostels\Pages\ListHostels;
use App\Filament\Resources\Hostels\Schemas\HostelForm;
use App\Filament\Resources\Hostels\Tables\HostelsTable;
use App\Models\Hostel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HostelResource extends Resource
{
    protected static ?string $model = Hostel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'hostel';

    public static function form(Schema $schema): Schema
    {
        return HostelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HostelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHostels::route('/'),
            'create' => CreateHostel::route('/create'),
            'edit' => EditHostel::route('/{record}/edit'),
        ];
    }
}
