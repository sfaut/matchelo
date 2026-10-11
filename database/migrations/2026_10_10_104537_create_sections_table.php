<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 50)->comment('Nom de la section (ex. Section générale, Section jeunes)');
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->unique(['name', 'club_id']);
            $table->comment("Sections d'un club : au moins une, la section générale créée avec le club");
        });

        Schema::create('section_user', function (Blueprint $table) {
            $table->foreignId('section_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->primary(['section_id', 'user_id']);
            $table->comment('Membres des sections, avec leur rôle dans la section : un joueur appartient à zéro, une ou plusieurs sections');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_user');
        Schema::dropIfExists('sections');
    }
};
