<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 50);
            $table->string('code', length: 10)->unique()->comment("Code d'invitation à donner aux élèves (ex. JM-4821)");
            $table->string('city', length: 50)->nullable()->comment('Ville du club');
            $table->string('description')->nullable()->comment('Jour, heure et lieu des séances');
            $table->string('logo_path')->nullable()->comment('Chemin du logo sur le disque public');
            $table->foreignId('user_id')->comment('Créateur du club')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps(precision: 6);
        });

        Schema::create('club_user', function (Blueprint $table) {
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->primary(['club_id', 'user_id']);
            $table->timestamps(precision: 6);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_user');
        Schema::dropIfExists('clubs');
    }
};
