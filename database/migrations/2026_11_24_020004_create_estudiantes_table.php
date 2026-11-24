<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('carrera_id')
                ->constrained('carreras')
                ->restrictOnDelete();

            $table->foreignId('sede_id')
                ->constrained('sedes')
                ->restrictOnDelete();

            $table->string('cedula', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo_institucional')->unique();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};