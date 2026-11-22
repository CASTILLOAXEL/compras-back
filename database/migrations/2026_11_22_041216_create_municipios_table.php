<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos', 'id', 'fk_municipios_departamento');
            $table->string('nombre', 100);
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['departamento_id', 'nombre'], 'uq_municipios_departamento_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
