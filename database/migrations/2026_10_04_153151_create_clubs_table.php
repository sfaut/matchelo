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

        Schema::create('membership_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 20)->unique();
            $table->string('name', length: 20)->unique();
            $table->string('description', length: 100)->default('');
            $table->unsignedTinyInteger('display_order')->default(0)->comment("Ordre d'affichage (menus de choix), par dizaines pour faciliter les insertions");
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Statuts d'une adhésion, de la demande au départ du club");
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('role_id')->comment('Rôle du membre au club')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('membership_status_id')->comment("Statut courant de l'adhésion, historisé dans membership_status_changes")->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('joined_at')->useCurrent()->comment("Date d'adhésion au club");
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->unique(['club_id', 'user_id']);
            $table->comment("Adhésions : un membre d'un club, avec son rôle et son statut au club");
        });

        Schema::create('membership_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('membership_status_id')->comment("Statut pris par l'adhésion")->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('changed_at')->useCurrent()->comment('Date du changement de statut');
            $table->foreignId('user_id')->comment('Auteur du changement')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Historique des statuts d'une adhésion : une ligne par changement, statut initial compris");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_status_changes');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_statuses');
        Schema::dropIfExists('clubs');
    }
};
