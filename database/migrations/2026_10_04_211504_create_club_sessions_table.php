<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('scheduled_at', precision: 0)->comment('Horodatage de la séance');
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->unique(['club_id', 'scheduled_at']);
            $table->comment("Séances d'un club, une par date");
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->foreignId('club_session_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('created_at', precision: 6)->useCurrent();
            $table->dateTime('updated_at', precision: 6)->useCurrent()->useCurrentOnUpdate();

            $table->primary(['club_session_id', 'user_id']);
            $table->comment('Présences : une ligne par membre présent à une séance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('club_sessions');
    }
};
