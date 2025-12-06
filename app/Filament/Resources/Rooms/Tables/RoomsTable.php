<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('room_number')
                    ->label('№')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('capacity')
                    ->label('Вместимость')
                    ->numeric()
                    ->suffix(' чел.')
                    ->icon('heroicon-o-users'),

                TextColumn::make('price_per_night')
                    ->label('Цена за ночь')
                    ->money('rub')
                    ->sortable(),

                ImageColumn::make('photos')
                    ->label('Фото')
                    ->stacked()
                    ->limit(3)
                    ->circular(),

                IconColumn::make('is_active')
                    ->label('На сайте')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),

                TextColumn::make('created_at')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Активные',
                        '0' => 'Скрытые',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Активировать')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Скрыть')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Добавить комнату'),
            ]);
    }
}
