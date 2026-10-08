<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taktik_assessments', function (Blueprint $table) {
            $table->id();

            // ---- RELASI ----
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // pelatih
            $table->foreignId('athlete_id')->nullable()->constrained()->nullOnDelete();

            // ---- IDENTITAS ----
            $table->string('name');
            $table->dateTime('assessment_date');

            // ---- KATEGORI TAKTIK (Skala 1-5) ----
            $table->integer('dribbling')->nullable();             // 1-5: Kemampuan dribbling
            $table->string('dribbling_note')->nullable();
            $table->integer('passing')->nullable();               // 1-5: Kemampuan passing
            $table->string('passing_note')->nullable();
            $table->integer('shooting')->nullable();              // 1-5: Kemampuan shooting
            $table->string('shooting_note')->nullable();
            $table->integer('positioning')->nullable();           // 1-5: Posisi dalam lapangan
            $table->string('positioning_note')->nullable();
            $table->integer('game_reading')->nullable();          // 1-5: Pembacaan permainan
            $table->string('game_reading_note')->nullable();
            $table->integer('decision_making')->nullable();       // 1-5: Pengambilan keputusan
            $table->string('decision_making_note')->nullable();
            $table->integer('defensive_skill')->nullable();       // 1-5: Kemampuan bertahan
            $table->string('defensive_skill_note')->nullable();
            $table->integer('set_pieces')->nullable();            // 1-5: Set pieces (tendangan sudut, tendangan bebas)
            $table->string('set_pieces_note')->nullable();

            // ---- RINGKASAN & REKOMENDASI ----
            $table->text('overall_assessment')->nullable();       // Narasi keseluruhan
            $table->text('recommendations')->nullable();          // Rekomendasi untuk peningkatan
            $table->text('formation_notes')->nullable();          // Catatan formasi dan taktik khusus

            // ---- TIMESTAMPS ----
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taktik_assessments');
    }
};
