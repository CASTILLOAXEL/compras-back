<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aldeas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios', 'id', 'fk_aldeas_municipio');
            $table->string('nombre', 100);
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
// No se repite el nombre de la aldea dentro del mismo municipio
            $table->unique(['municipio_id', 'nombre'], 'uq_aldeas_municipio_nombre');
            $table->unique(['id', 'municipio_id'], 'uq_aldeas_id_municipio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aldeas');
    }
};
