<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20);
            $table->date('fecha');
            $table->foreignId('solicitante_id')->constrained('usuarios', 'id', 'fk_solicitudes_solicitante');
            $table->foreignId('centro_costo_id')->constrained('centros_costo', 'id', 'fk_solicitudes_centro_costo');
            $table->foreignId('estado_solicitud_id')->constrained('estados_solicitud', 'id', 'fk_solicitudes_estado');
            $table->timestamps();
            $table->softDeletes();
        });


        DB::statement('CREATE UNIQUE INDEX uq_solicitudes_numero ON solicitudes (numero) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
