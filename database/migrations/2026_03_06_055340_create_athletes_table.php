<?php
// database/migrations/2024_01_01_000002_create_athletes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();      // putra / putri
            $table->string('position')->nullable();    // Striker, Gelandang, dll
            $table->decimal('height', 5, 1)->nullable(); // cm
            $table->decimal('weight', 5, 1)->nullable(); // kg
            $table->string('phone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('athletes');
    }
};
