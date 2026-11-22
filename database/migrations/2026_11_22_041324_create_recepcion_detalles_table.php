<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada ítem recibido pertenece a la misma orden que la recepción.
        // orden_id es redundancia controlada, con el mismo criterio que orden_detalles.
        Schema::create('recepcion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id');
            $table->foreignId('orden_id');
            $table->foreignId('orden_detalle_id');
            $table->decimal('cantidad_recibida', 12, 2);
            $table->timestamps();
            $table->softDeletes();

            // Un ítem se registra una sola vez por recepción
            $table->unique(['recepcion_id', 'orden_detalle_id'], 'uq_recepcion_detalles_item');

            $table->foreign(['recepcion_id', 'orden_id'], 'fk_recepcion_detalles_recepcion')
                ->references(['id', 'orden_id'])
                ->on('recepciones');

            $table->foreign(['orden_detalle_id', 'orden_id'], 'fk_recepcion_detalles_orden_detalle')
                ->references(['id', 'orden_id'])
                ->on('orden_detalles');
        });

        DB::statement('ALTER TABLE recepcion_detalles ADD CONSTRAINT ck_recepcion_detalles_cantidad CHECK (cantidad_recibida > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_detalles');
    }
};
