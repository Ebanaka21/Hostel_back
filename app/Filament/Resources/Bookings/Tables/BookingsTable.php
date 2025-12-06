<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guest_surname')->label('Фамилия')->searchable(),
                TextColumn::make('guest_name')->label('Имя')->searchable(),
                TextColumn::make('room.name')->label('Комната'),
                TextColumn::make('check_in_date')->label('Заезд')->date('d.m.Y'),
                TextColumn::make('check_out_date')->label('Выезд')->date('d.m.Y'),
                TextColumn::make('total_price')->label('Сумма')->money('rub'),
                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'pending_payment',
                        'success' => 'paid',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending_payment' => 'Ожидает оплаты',
                    'paid' => 'Оплачено',
                    'cancelled' => 'Отменено',
                ]),
            ])
            ->actions([
                // КНОПКА ОПЛАТИТЬ — ВОТ ОНА!
                Action::make('pay')
                    ->label('Оплатить')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->button()
                    ->visible(fn(Booking $record) => $record->status === 'pending_payment')
                    ->requiresConfirmation()
                    ->modalHeading('Подтвердить оплату')
                    ->modalDescription('Гость оплатил бронирование?')
                    ->modalSubmitActionLabel('Да, оплатить')
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'paid']);

                        Notification::make()
                            ->title('Бронь №' . $record->id . ' оплачена')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
