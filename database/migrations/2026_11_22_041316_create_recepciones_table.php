<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una orden puede recibirse en varias entregas
        Schema::create('recepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes', 'id', 'fk_recepciones_orden');
            $table->foreignId('responsable_id')->constrained('usuarios', 'id', 'fk_recepciones_responsable');
            $table->date('fecha');
            $table->string('observaciones', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Lo usa recepcion_detalles para validar que la recepción sea de la misma orden
            $table->unique(['id', 'orden_id'], 'uq_recepciones_id_orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepciones');
    }
};
