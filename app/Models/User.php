<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Поля, которые можно массово заполнять
     */
    protected $fillable = [
        'name',                  // Имя
        'surname',               // Фамилия
        'second_name',           // Отчество

        'passport_series',
        'passport_number',
        'passport_issued_at',
        'passport_issued_by',
        'passport_code',

        'birthday',
        'gender',                // male | female

        'phone',
        'email',
        'password',
    ];

    /**
     * Поля, которые скрываются при сериализации (в API)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Касты атрибутов
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'passport_issued_at' => 'date',
            'birthday' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Полное ФИО (удобный аксессор)
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->surname} {$this->name} {$this->second_name}");
    }

    /**
     * JWT: идентификатор пользователя
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT: дополнительные claims (можно добавить роль, права и т.д.)
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'user_id' => $this->id,
            'email'   => $this->email,
            'name'    => $this->getFullNameAttribute(),
            // 'role' => $this->role ?? 'guest', // если добавишь роли позже
        ];
    }

    /**
     * Отношения
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Проверка, заполнены ли паспортные данные
     */
    public function hasPassportData(): bool
    {
        return !empty($this->passport_series) &&
               !empty($this->passport_number) &&
               !empty($this->passport_issued_at);
    }
}
