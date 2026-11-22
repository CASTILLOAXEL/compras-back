<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios', 'id', 'fk_direcciones_municipio');
            $table->unsignedBigInteger('aldea_id')->nullable();
            $table->string('detalle', 250);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['aldea_id', 'municipio_id'], 'fk_direcciones_aldea')
                ->references(['id', 'municipio_id'])
                ->on('aldeas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};
