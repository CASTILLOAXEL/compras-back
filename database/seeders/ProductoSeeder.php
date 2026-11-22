<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $tipoBienId = DB::table('tipos_producto')->where('nombre', 'Bien')->value('id');
        $tipoServicioId = DB::table('tipos_producto')->where('nombre', 'Servicio')->value('id');

        $this->insertarConFechas('productos', [
            ['tipo_producto_id' => $tipoBienId, 'nombre' => 'Papel bond tamaño carta'],
            ['tipo_producto_id' => $tipoBienId, 'nombre' => 'Tóner para impresora'],
            ['tipo_producto_id' => $tipoBienId, 'nombre' => 'Computadora portátil'],
            ['tipo_producto_id' => $tipoBienId, 'nombre' => 'Silla ergonómica'],
            ['tipo_producto_id' => $tipoServicioId, 'nombre' => 'Mantenimiento de aire acondicionado'],
            ['tipo_producto_id' => $tipoServicioId, 'nombre' => 'Servicio de limpieza'],
            ['tipo_producto_id' => $tipoServicioId, 'nombre' => 'Soporte de red'],
        ]);
    }
}
