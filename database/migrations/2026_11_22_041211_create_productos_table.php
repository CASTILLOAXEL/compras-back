<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de bienes y servicios que se pueden solicitar
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_producto_id')->constrained('tipos_producto', 'id', 'fk_productos_tipo');
            $table->string('nombre', 150)->unique('uq_productos_nombre');
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
