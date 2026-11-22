<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orden de compra: cero o una por solicitud aprobada
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20);
            $table->date('fecha_emision');
            $table->foreignId('solicitud_id')->constrained('solicitudes', 'id', 'fk_ordenes_solicitud');
            $table->foreignId('proveedor_id')->constrained('proveedores', 'id', 'fk_ordenes_proveedor');
            $table->foreignId('estado_orden_id')->constrained('estados_orden', 'id', 'fk_ordenes_estado');
            $table->timestamps();
            $table->softDeletes();

            // Lo usa orden_detalles para validar que la orden sea de la misma solicitud
            $table->unique(['id', 'solicitud_id'], 'uq_ordenes_id_solicitud');
        });

        // Número único y una sola orden activa por solicitud
        DB::statement('CREATE UNIQUE INDEX uq_ordenes_numero ON ordenes (numero) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX uq_ordenes_solicitud ON ordenes (solicitud_id) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
