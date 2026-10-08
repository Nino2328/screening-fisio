<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Samakan nama role: 'fisioterapis' -> 'fisio'
     * (route middleware 'role:fisio', seeder, dan sidebar memakai 'fisio')
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('fisio')->change();
        });

        DB::table('users')
            ->where('role', 'fisioterapis')
            ->update(['role' => 'fisio']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'fisio')
            ->update(['role' => 'fisioterapis']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('fisioterapis')->change();
        });
    }
};
