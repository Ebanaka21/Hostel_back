<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Основные данные
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('second_name')->nullable();

            // Паспортные данные
            $table->string('passport_series', 10)->nullable();
            $table->string('passport_number', 20)->nullable();
            $table->date('passport_issued_at')->nullable();
            $table->string('passport_issued_by')->nullable();
            $table->string('passport_code', 20)->nullable();

            // Личные данные
            $table->date('birthday')->nullable();

            // Пол — через ENUM
            $table->enum('gender', ['male', 'female'])->nullable();

            // Контакты
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Остальное
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->longText('payload');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
