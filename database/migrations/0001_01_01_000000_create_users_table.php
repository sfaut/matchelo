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
            $table->timestamps(precision: 6);
        });

        Schema::create('sexes', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 10)->unique();
            $table->string('name', length: 10);
            $table->boolean('is_feminine')->default(false);
            $table->boolean('is_masculine')->default(false);
            $table->timestamps(precision: 6);
            $table->comment('Sexes');
        });

        Schema::create('civilities', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 10)->comment('Code de la civilité');
            $table->string('name', length: 10)->comment('Civilité en toutes lettres');
            $table->string('name_short', length: 10)->comment('Forme courte de la civilité');
            $table->foreignId('sex_id')->default(1)->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps(precision: 6);
            $table->comment('Civilités et leur sexe associé');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('last_name', length: 30)->comment('Nom de famille');
            $table->string('first_name', length: 30)->comment('Prénom');
            $table->foreignId('civility_id')->default(1)->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->boolean('is_administrator')->default(false)->comment("L'utilisateur est-il administrateur ?");
            $table->string('email', length: 100)->unique()->comment("E-mail de l'utilisateur");
            $table->dateTime('email_verified_at', precision: 6)->nullable()->comment("Horodatage de la confirmation de l'e-mail");
            $table->string('password_hash')->comment("Mot de passe haché de l'utilisateur");
            $table->rememberToken();
            $table->timestamps(precision: 6);
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
        Schema::dropIfExists('civilities');
        Schema::dropIfExists('sexes');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
