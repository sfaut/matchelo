<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 10)->unique();
            $table->string('name', length: 10);
            $table->string('description', length: 200)->default('');
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('last_name', length: 30)->comment('Nom de famille');
            $table->string('first_name', length: 30)->comment('Prénom');
            $table->boolean('is_administrator')->default(false)->comment("L'utilisateur est-il administrateur ?");
            $table->unsignedSmallInteger('elo_rating')->default(1200)->comment('Classement ELO, unique tous clubs confondus');
            // Exception à la règle « pas de nullable » : NULL = joueur sans compte (créé par un prof),
            // l'unicité reste garantie car les NULL ne se heurtent pas
            $table->string('email', length: 100)->nullable()->unique()->comment("E-mail de l'utilisateur, NULL pour un joueur sans compte");
            $table->dateTime('email_verified_at', precision: 6)->nullable()->comment("Horodatage de la confirmation de l'e-mail");
            $table->string('password_hash', length: 100)->default('')->comment("Mot de passe haché de l'utilisateur, vide pour un joueur sans compte");
            $table->rememberToken();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', length: 100)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
