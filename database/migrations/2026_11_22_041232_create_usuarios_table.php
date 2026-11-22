<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personal autorizado: solicitantes y responsables de recepción
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->char('dpi', 13);
            $table->string('primer_nombre', 50);
            $table->string('segundo_nombre', 50)->nullable();
            $table->string('tercer_nombre', 50)->nullable();
            $table->string('primer_apellido', 50);
            $table->string('segundo_apellido', 50)->nullable();
            $table->string('tercer_apellido', 50)->nullable();
            $table->string('correo', 150);
            $table->string('password');
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones', 'id', 'fk_usuarios_direccion');
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // DPI: exactamente 13 dígitos
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT ck_usuarios_dpi CHECK (LEN(dpi) = 13 AND dpi NOT LIKE '%[^0-9]%')");

        // Únicos solo entre registros no eliminados
        DB::statement('CREATE UNIQUE INDEX uq_usuarios_dpi ON usuarios (dpi) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX uq_usuarios_correo ON usuarios (correo) WHERE deleted_at IS NULL');

        // Cada dirección pertenece a un solo usuario (la dirección es opcional)
        DB::statement('CREATE UNIQUE INDEX uq_usuarios_direccion ON usuarios (direccion_id) WHERE direccion_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
