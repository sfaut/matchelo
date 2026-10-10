<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', length: 50)->comment('Nom de la section (ex. Section générale, Section jeunes)');
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->unique(['club_id', 'name']);
            $table->comment("Sections d'un club : au moins une, la section générale créée avec le club");
        });

        Schema::create('club_section_user', function (Blueprint $table) {
            $table->foreignId('club_section_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->primary(['club_section_id', 'user_id']);
            $table->comment('Joueurs des sections : un joueur appartient à zéro, une ou plusieurs sections');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_section_user');
        Schema::dropIfExists('club_sections');
    }
};
