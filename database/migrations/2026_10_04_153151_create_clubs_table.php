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
            $table->string('city', length: 50)->default('')->comment('Ville du club (vide si non renseignée)');
            $table->string('description', length: 300)->default('')->comment('Jour, heure et lieu des séances (vide si non renseignés)');
            $table->string('logo_path', length: 100)->default('')->comment('Chemin du logo sur le disque public (vide sans logo)');
            $table->foreignId('user_id')->comment('Créateur du club')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('role_id')->comment('Rôle du membre au club')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('joined_at', precision: 6)->useCurrent()->comment("Horodatage de l'adhésion au club");
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->unique(['club_id', 'user_id']);
            $table->comment("Adhésions : un membre d'un club, avec son rôle au club");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('clubs');
    }
};
