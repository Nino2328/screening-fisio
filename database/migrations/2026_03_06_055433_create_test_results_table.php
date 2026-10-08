<?php
// database/migrations/2024_01_01_000003_create_test_results_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();

            // ---- RELASI ----
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // fisioterapis
            $table->foreignId('athlete_id')->nullable()->constrained()->nullOnDelete();

            // ---- IDENTITAS (disimpan langsung untuk historis) ----
            $table->string('test_type');  // endurance|speed|agility|strength|power|accuracy|reaction|coordination|flexibility|balance
            $table->string('name');
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('position')->nullable();
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->string('phone', 20)->nullable();
            $table->dateTime('test_date');

            // ---- VITAL SIGN ----
            $table->string('vital_td')->nullable();
            $table->string('vital_suhu')->nullable();
            $table->string('vital_nadi')->nullable();
            $table->string('vital_respirasi')->nullable();
            $table->text('complaint')->nullable();

            // ---- 1. ENDURANCE ----
            $table->decimal('distance', 8, 2)->nullable();   // meter
            $table->string('level')->nullable();             // 17.3

            // ---- 2. SPEED ----
            $table->decimal('time_seconds', 6, 2)->nullable();
            $table->integer('score')->nullable();            // 1-5

            // ---- 3. AGILITY ----
            $table->decimal('hex_time1', 6, 2)->nullable();
            $table->decimal('hex_time2', 6, 2)->nullable();
            $table->decimal('hex_avg', 6, 2)->nullable();
            $table->string('hex_rating')->nullable();
            $table->decimal('ill_time', 6, 2)->nullable();
            $table->string('ill_rating')->nullable();

            // ---- 4. STRENGTH ----
            $table->integer('pushup_count')->nullable();
            $table->integer('pushup_score')->nullable();
            $table->string('pushup_rating')->nullable();
            $table->text('pushup_note')->nullable();
            $table->integer('situp_count')->nullable();
            $table->integer('situp_score')->nullable();
            $table->string('situp_rating')->nullable();
            $table->text('situp_note')->nullable();

            // ---- 5. POWER ----
            $table->decimal('try1', 7, 2)->nullable();
            $table->decimal('try2', 7, 2)->nullable();
            $table->decimal('try3', 7, 2)->nullable();
            $table->decimal('best', 7, 2)->nullable();      // cm

            // ---- 6. ACCURACY ----
            $table->decimal('shot1_score', 5, 1)->nullable();
            $table->decimal('shot1_time', 5, 2)->nullable();
            $table->string('shot1_note')->nullable();
            $table->decimal('shot2_score', 5, 1)->nullable();
            $table->decimal('shot2_time', 5, 2)->nullable();
            $table->string('shot2_note')->nullable();
            $table->decimal('shot3_score', 5, 1)->nullable();
            $table->decimal('shot3_time', 5, 2)->nullable();
            $table->string('shot3_note')->nullable();
            $table->decimal('total_score', 6, 1)->nullable();

            // ---- 7. REACTION ----
            $table->decimal('value_cm', 6, 2)->nullable();  // cm drop

            // ---- 8. COORDINATION ----
            $table->decimal('steps50', 5, 1)->nullable();  // derajat
            $table->string('steps50_result')->nullable();   // normal/abnormal
            $table->decimal('steps100', 5, 1)->nullable();
            $table->string('steps100_result')->nullable();
            $table->string('rotation_direction')->nullable();

            // ---- 9. FLEXIBILITY ----
            // try1, try2, try3, best sudah ada di atas (dipakai bersama power juga)
            // Tambah prefix jika perlu, atau gunakan kolom yg sama (best = cm terbaik)

            // ---- 10. BALANCE (YBT) ----
            $table->decimal('leg_length', 5, 1)->nullable();
            $table->decimal('left_a', 6, 2)->nullable();
            $table->decimal('left_pm', 6, 2)->nullable();
            $table->decimal('left_pl', 6, 2)->nullable();
            $table->decimal('right_a', 6, 2)->nullable();
            $table->decimal('right_pm', 6, 2)->nullable();
            $table->decimal('right_pl', 6, 2)->nullable();
            $table->string('res_left_anterior')->nullable();
            $table->string('res_right_anterior')->nullable();
            $table->string('res_diff_anterior')->nullable();
            $table->string('res_left_pm')->nullable();
            $table->string('res_right_pm')->nullable();
            $table->string('res_diff_pm')->nullable();
            $table->string('res_left_pl')->nullable();
            $table->string('res_right_pl')->nullable();
            $table->string('res_diff_pl')->nullable();
            $table->string('composite_left')->nullable();
            $table->string('composite_right')->nullable();
            $table->string('std_anterior')->nullable();
            $table->string('std_pm')->nullable();
            $table->string('std_pl')->nullable();
            $table->string('std_composite')->nullable();

            // ---- SHARED ----
            $table->string('rating')->nullable();           // rating akhir
            $table->text('conclusion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index untuk query cepat
            $table->index(['test_type', 'name']);
            $table->index('test_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
