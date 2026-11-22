<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Proveedores de prueba, cada uno con su dirección.
 * Si un NIT ya existe, lo salta: se puede correr varias veces sin duplicar.
 */
class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'razon_social' => 'Distribuidora El Progreso, S.A.',
                'nit' => '1234567-8',
                'dpi' => null,
                'persona_contacto' => 'Carlos Pérez',
                'telefono' => '55551234',
                'correo' => 'ventas@elprogreso.com.gt',
                'estado' => true,
                'municipio' => 'Guatemala',
                'detalle' => '6a. avenida 12-45 zona 1',
            ],
            [
                'razon_social' => 'Tecnología Integral, S.A.',
                'nit' => '9876543-2',
                'dpi' => null,
                'persona_contacto' => 'María Fernanda López',
                'telefono' => '22334455',
                'correo' => 'cotizaciones@tecnointegral.com.gt',
                'estado' => true,
                'municipio' => 'Mixco',
                'detalle' => 'Calzada Roosevelt 22-43 zona 11',
            ],
            [
                'razon_social' => 'Librería y Papelería Oriente',
                'nit' => '4567891-K',
                'dpi' => null,
                'persona_contacto' => 'José Ramírez',
                'telefono' => '78441236',
                'correo' => 'pedidos@papeleriaoriente.com',
                'estado' => true,
                'municipio' => 'Jutiapa',
                'detalle' => '3a. calle 4-25 zona 1',
            ],
            [
                'razon_social' => 'Servicios de Limpieza Brillo Total',
                'nit' => '3216549-7',
                'dpi' => null,
                'persona_contacto' => 'Ana Lucía Morales',
                'telefono' => '41236587',
                'correo' => 'contacto@brillototal.com',
                'estado' => true,
                'municipio' => 'Escuintla',
                'detalle' => '2a. avenida 8-10 zona 2',
            ],
            [
                'razon_social' => 'Ferretería La Económica',
                'nit' => '7418529-6',
                'dpi' => null,
                'persona_contacto' => 'Luis Hernández',
                'telefono' => '79521478',
                'correo' => 'ventas@laeconomica.com.gt',
                'estado' => true,
                'municipio' => 'Chiquimula',
                'detalle' => '5a. calle 3-60 zona 1',
            ],
            [
                // Persona individual: además del NIT tiene DPI
                'razon_social' => 'Roberto Castillo Méndez',
                'nit' => '8529637-1',
                'dpi' => '2547896310101',
                'persona_contacto' => 'Roberto Castillo Méndez',
                'telefono' => '53698741',
                'correo' => 'rcastillo.mantenimiento@gmail.com',
                'estado' => true,
                'municipio' => 'Quetzaltenango',
                'detalle' => '14 avenida 2-35 zona 3',
            ],
            [
                'razon_social' => 'Mobiliario y Equipo Cobán',
                'nit' => '1597534-0',
                'dpi' => null,
                'persona_contacto' => 'Sandra Caal',
                'telefono' => '79514862',
                'correo' => 'ventas@mobiliariocoban.com',
                'estado' => true,
                'municipio' => 'Cobán',
                'detalle' => '1a. calle 5-15 zona 1',
            ],
            [
                // Inactivo: no aparece al generar órdenes ni contratos
                'razon_social' => 'Suministros Zacapa (inactivo)',
                'nit' => '7539514-K',
                'dpi' => null,
                'persona_contacto' => 'Pedro Orellana',
                'telefono' => '79412563',
                'correo' => 'info@suministroszacapa.com',
                'estado' => false,
                'municipio' => 'Zacapa',
                'detalle' => '4a. avenida 6-20 zona 1',
            ],
        ];

        $fechaActual = now();

        foreach ($proveedores as $proveedor) {
            if (DB::table('proveedores')->where('nit', $proveedor['nit'])->exists()) {
                continue;
            }

            $direccionId = DB::table('direcciones')->insertGetId([
                'municipio_id' => $this->municipioId($proveedor['municipio']),
                'aldea_id' => null,
                'detalle' => $proveedor['detalle'],
                'created_at' => $fechaActual,
                'updated_at' => $fechaActual,
            ]);

            DB::table('proveedores')->insert([
                'razon_social' => $proveedor['razon_social'],
                'nit' => $proveedor['nit'],
                'dpi' => $proveedor['dpi'],
                'persona_contacto' => $proveedor['persona_contacto'],
                'telefono' => $proveedor['telefono'],
                'correo' => $proveedor['correo'],
                'estado' => $proveedor['estado'],
                'direccion_id' => $direccionId,
                'created_at' => $fechaActual,
                'updated_at' => $fechaActual,
            ]);
        }
    }

    /**
     * Busca el municipio por nombre; si no lo encuentra, usa el primero para no detener el seeder.
     */
    private function municipioId(string $nombreMunicipio): int
    {
        return DB::table('municipios')->where('nombre', $nombreMunicipio)->value('id')
            ?? DB::table('municipios')->orderBy('id')->value('id');
    }
}
