<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 20)->unique();
            $table->string('name', length: 20)->unique();
            $table->string('description', length: 100)->default('');
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Types d'événements, cours, tournoi, etc.");
        });

        Schema::create('event_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 20)->unique();
            $table->string('name', length: 20)->unique();
            $table->string('description', length: 100)->default('');
            $table->unsignedTinyInteger('display_order')->default(0)->comment("Ordre d'affichage (menus de choix), par dizaines pour faciliter les insertions");
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Statuts d'un événement, du brouillon à la clôture du pointage");
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->dateTime('scheduled_at', precision: 0)->comment("Horodatage de l'événement");
            $table->string('description', length: 100)->default('');
            $table->foreignId('event_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('event_status_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Événements d'un club : cours, tournois…");
        });

        Schema::create('event_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('event_status_id')->comment("Statut pris par l'événement")->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('changed_at', precision: 6)->useCurrent()->comment('Horodatage du changement de statut');
            $table->foreignId('user_id')->comment('Auteur du changement')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Historique des statuts d'un événement : une ligne par changement, statut initial compris");
        });

        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 20)->unique();
            $table->string('name', length: 20)->unique();
            $table->string('description', length: 100)->default('');
            $table->unsignedTinyInteger('display_order')->default(0)->comment("Ordre d'affichage (menus de choix), par dizaines pour faciliter les insertions");
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment('Statuts de présence aux événements');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('role_id')->comment("Rôle courant tenu à l'événement (animateur, joueur, arbitre), historisé dans attendance_role_changes")->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('attendance_status_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->unique(['event_id', 'user_id']);
            $table->comment('Pointage : une ligne par membre pointé à un événement, avec son rôle et son statut de présence');
        });

        Schema::create('attendance_status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('attendance_status_id')->comment('Statut de présence pris')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('changed_at', precision: 6)->useCurrent()->comment('Horodatage du changement de statut');
            $table->foreignId('user_id')->comment('Auteur du changement')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Historique des statuts de présence d'un pointage : une ligne par changement, statut initial compris");
        });

        Schema::create('attendance_role_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('role_id')->comment("Rôle pris à l'événement")->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('changed_at', precision: 6)->useCurrent()->comment('Horodatage du changement de rôle');
            $table->foreignId('user_id')->comment('Auteur du changement')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment("Historique des rôles d'un participant à un événement : une ligne par changement, rôle initial compris");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_role_changes');
        Schema::dropIfExists('attendance_status_changes');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_statuses');
        Schema::dropIfExists('event_status_changes');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_statuses');
        Schema::dropIfExists('event_types');
    }
};
