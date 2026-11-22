<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El DPI aplica solo a proveedores individuales; las empresas se identifican por NIT
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 200);
            $table->string('nit', 15);
            $table->char('dpi', 13)->nullable();
            $table->string('persona_contacto', 150);
            $table->string('telefono', 15);
            $table->string('correo', 150);
            $table->foreignId('direccion_id')
                ->unique('uq_proveedores_direccion')
                ->constrained('direcciones', 'id', 'fk_proveedores_direccion');
            $table->boolean('estado')->default(true); // 1 = activo, 0 = inactivo
            $table->timestamps();
            $table->softDeletes();
        });

        // DPI opcional; si se registra, exactamente 13 dígitos
        DB::statement("ALTER TABLE proveedores ADD CONSTRAINT ck_proveedores_dpi CHECK (dpi IS NULL OR (LEN(dpi) = 13 AND dpi NOT LIKE '%[^0-9]%'))");

        // Únicos solo entre registros no eliminados (y el DPI solo cuando existe)
        DB::statement('CREATE UNIQUE INDEX uq_proveedores_nit ON proveedores (nit) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX uq_proveedores_dpi ON proveedores (dpi) WHERE dpi IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
