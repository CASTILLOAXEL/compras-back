<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pais_id')->constrained('paises', 'id', 'fk_departamentos_pais');
            $table->string('nombre', 100);
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['pais_id', 'nombre'], 'uq_departamentos_pais_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
