<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada ítem de la orden viene de un ítem de su misma solicitud.
        // solicitud_id es redundancia controlada: las dos llaves compuestas lo obligan a coincidir.
        Schema::create('orden_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id');
            $table->foreignId('solicitud_id');
            $table->foreignId('solicitud_detalle_id');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 13, 2);
            $table->timestamps();
            $table->softDeletes();

            // Un ítem solicitado aparece una sola vez en la orden
            $table->unique(['orden_id', 'solicitud_detalle_id'], 'uq_orden_detalles_item');

            // Lo usa recepcion_detalles para validar que el ítem sea de la misma orden
            $table->unique(['id', 'orden_id'], 'uq_orden_detalles_id_orden');

            $table->foreign(['orden_id', 'solicitud_id'], 'fk_orden_detalles_orden')
                ->references(['id', 'solicitud_id'])
                ->on('ordenes');

            $table->foreign(['solicitud_detalle_id', 'solicitud_id'], 'fk_orden_detalles_solicitud_detalle')
                ->references(['id', 'solicitud_id'])
                ->on('solicitud_detalles');
        });

        DB::statement('ALTER TABLE orden_detalles ADD CONSTRAINT ck_orden_detalles_cantidad CHECK (cantidad > 0)');
        DB::statement('ALTER TABLE orden_detalles ADD CONSTRAINT ck_orden_detalles_precio CHECK (precio_unitario >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_detalles');
    }
};
