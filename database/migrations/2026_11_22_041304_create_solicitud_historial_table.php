<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Trazabilidad: quién cambió el estado de la solicitud y cuándo
        Schema::create('solicitud_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')
                ->constrained('solicitudes', 'id', 'fk_solicitud_historial_solicitud')
                ->cascadeOnDelete();
            $table->foreignId('estado_solicitud_id')->constrained('estados_solicitud', 'id', 'fk_solicitud_historial_estado');
            $table->foreignId('usuario_id')->constrained('usuarios', 'id', 'fk_solicitud_historial_usuario');
            $table->string('observacion', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_historial');
    }
};
