<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('carrera_id')
                ->nullable()
                ->constrained('carreras')
                ->restrictOnDelete();

            $table->foreignId('sede_id')
                ->nullable()
                ->constrained('sedes')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropForeign(['sede_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'carrera_id',
                'sede_id',
            ]);
        });
    }
};