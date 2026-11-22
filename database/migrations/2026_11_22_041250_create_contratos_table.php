<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores', 'id', 'fk_contratos_proveedor');
            $table->foreignId('tipo_producto_id')->constrained('tipos_producto', 'id', 'fk_contratos_tipo'); // bien o servicio
            $table->foreignId('estado_contrato_id')->constrained('estados_contrato', 'id', 'fk_contratos_estado');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('monto', 13, 2);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE contratos ADD CONSTRAINT ck_contratos_fechas CHECK (fecha_fin >= fecha_inicio)');
        DB::statement('ALTER TABLE contratos ADD CONSTRAINT ck_contratos_monto CHECK (monto > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
