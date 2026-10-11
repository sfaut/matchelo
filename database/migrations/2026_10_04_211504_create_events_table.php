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
            $table->unsignedSmallInteger('display_order')->default(0)->comment("Ordre d'affichage (menus de choix), par dizaines pour faciliter les insertions");
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

        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', length: 20)->unique();
            $table->string('name', length: 20)->unique();
            $table->string('description', length: 100)->default('');
            $table->unsignedSmallInteger('display_order')->default(0)->comment("Ordre d'affichage (menus de choix), par dizaines pour faciliter les insertions");
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->comment('Statuts de présence aux événements');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('attendance_status_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->primary(['event_id', 'user_id']);
            $table->comment('Pointage : une ligne par membre pointé à un événement, avec son statut de présence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_statuses');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_statuses');
        Schema::dropIfExists('event_types');
    }
};
