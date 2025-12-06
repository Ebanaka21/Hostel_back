<?php

namespace App\Filament\Resources\Hostels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HostelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о хостеле')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label('Адрес')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->required(),
                    ]),
            ]);
    }
}
