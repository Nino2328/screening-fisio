<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mental_assessments', function (Blueprint $table) {
            $table->id();

            // ---- RELASI ----
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // pelatih
            $table->foreignId('athlete_id')->nullable()->constrained()->nullOnDelete();

            // ---- IDENTITAS ----
            $table->string('name');
            $table->dateTime('assessment_date');

            // ---- KATEGORI MENTAL (Skala 1-5) ----
            $table->integer('confidence')->nullable();           // 1-5: Kepercayaan diri
            $table->string('confidence_note')->nullable();
            $table->integer('focus')->nullable();                // 1-5: Konsentrasi
            $table->string('focus_note')->nullable();
            $table->integer('anxiety')->nullable();              // 1-5: Kecemasan (1=sangat cemas, 5=santai)
            $table->string('anxiety_note')->nullable();
            $table->integer('motivation')->nullable();           // 1-5: Motivasi
            $table->string('motivation_note')->nullable();
            $table->integer('team_spirit')->nullable();          // 1-5: Semangat tim
            $table->string('team_spirit_note')->nullable();
            $table->integer('pressure_handling')->nullable();    // 1-5: Penanganan tekanan
            $table->string('pressure_handling_note')->nullable();

            // ---- RINGKASAN & REKOMENDASI ----
            $table->text('overall_assessment')->nullable();      // Narasi keseluruhan
            $table->text('recommendations')->nullable();          // Rekomendasi untuk peningkatan

            // ---- TIMESTAMPS ----
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mental_assessments');
    }
};
