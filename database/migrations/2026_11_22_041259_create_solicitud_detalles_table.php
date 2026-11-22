<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ítems de la solicitud: se borran junto con la solicitud
        Schema::create('solicitud_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')
                ->constrained('solicitudes', 'id', 'fk_solicitud_detalles_solicitud')
                ->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos', 'id', 'fk_solicitud_detalles_producto');
            $table->foreignId('unidad_medida_id')->constrained('unidades_medida', 'id', 'fk_solicitud_detalles_unidad');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_estimado', 13, 2);
            $table->timestamps();
            $table->softDeletes();

            // Lo usa orden_detalles para validar que el ítem sea de la misma solicitud
            $table->unique(['id', 'solicitud_id'], 'uq_solicitud_detalles_id_solicitud');
        });

        DB::statement('ALTER TABLE solicitud_detalles ADD CONSTRAINT ck_solicitud_detalles_cantidad CHECK (cantidad > 0)');
        DB::statement('ALTER TABLE solicitud_detalles ADD CONSTRAINT ck_solicitud_detalles_precio CHECK (precio_estimado >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_detalles');
    }
};
