<?php

namespace Database\Seeders;

use App\Models\EstadoContrato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contratos de prueba con proveedores activos: bienes y servicios en sus tres estados.
 * Requiere ProveedorSeeder, TipoProductoSeeder y EstadoContratoSeeder.
 * Si ya hay contratos, no hace nada, para no duplicar.
 */
class ContratoSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('contratos')->exists()) {
            $this->command->warn('Ya existen contratos; no se agregaron más.');

            return;
        }

        $proveedoresIds = DB::table('proveedores')
            ->where('estado', true)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (! $proveedoresIds) {
            $this->command->warn('No hay proveedores activos: corra antes ProveedorSeeder.');

            return;
        }

        $tipoBienId = $this->tipoProductoId('Bien');
        $tipoServicioId = $this->tipoProductoId('Servicio');
        $hoy = now()->startOfDay();

        // Fechas relativas a hoy: los vigentes siguen en curso y los finalizados ya vencieron
        $contratos = [
            [
                'proveedor' => 0,
                'tipo_producto_id' => $tipoBienId,
                'estado_contrato_id' => EstadoContrato::VIGENTE,
                'fecha_inicio' => $hoy->copy()->subMonths(2),
                'fecha_fin' => $hoy->copy()->addMonths(10),
                'monto' => 85000.00,
            ],
            [
                'proveedor' => 1,
                'tipo_producto_id' => $tipoServicioId,
                'estado_contrato_id' => EstadoContrato::VIGENTE,
                'fecha_inicio' => $hoy->copy()->subMonth(),
                'fecha_fin' => $hoy->copy()->addMonths(11),
                'monto' => 36000.00,
            ],
            [
                'proveedor' => 3,
                'tipo_producto_id' => $tipoServicioId,
                'estado_contrato_id' => EstadoContrato::VIGENTE,
                'fecha_inicio' => $hoy->copy()->startOfYear(),
                'fecha_fin' => $hoy->copy()->endOfYear(),
                'monto' => 18500.50,
            ],
            [
                'proveedor' => 2,
                'tipo_producto_id' => $tipoBienId,
                'estado_contrato_id' => EstadoContrato::FINALIZADO,
                'fecha_inicio' => $hoy->copy()->subYear()->startOfYear(),
                'fecha_fin' => $hoy->copy()->subYear()->endOfYear(),
                'monto' => 42750.00,
            ],
            [
                'proveedor' => 5,
                'tipo_producto_id' => $tipoServicioId,
                'estado_contrato_id' => EstadoContrato::FINALIZADO,
                'fecha_inicio' => $hoy->copy()->subMonths(8),
                'fecha_fin' => $hoy->copy()->subMonths(2),
                'monto' => 12000.00,
            ],
            [
                'proveedor' => 4,
                'tipo_producto_id' => $tipoBienId,
                'estado_contrato_id' => EstadoContrato::ANULADO,
                'fecha_inicio' => $hoy->copy()->subMonths(3),
                'fecha_fin' => $hoy->copy()->addMonths(9),
                'monto' => 27300.00,
            ],
        ];

        $fechaActual = now();

        foreach ($contratos as $contrato) {
            DB::table('contratos')->insert([
                // Si hay menos proveedores, vuelve a empezar desde el primero
                'proveedor_id' => $proveedoresIds[$contrato['proveedor'] % count($proveedoresIds)],
                'tipo_producto_id' => $contrato['tipo_producto_id'],
                'estado_contrato_id' => $contrato['estado_contrato_id'],
                'fecha_inicio' => $contrato['fecha_inicio']->toDateString(),
                'fecha_fin' => $contrato['fecha_fin']->toDateString(),
                'monto' => $contrato['monto'],
                'created_at' => $fechaActual,
                'updated_at' => $fechaActual,
            ]);
        }

        $this->command->info('Contratos de prueba creados: '.count($contratos).'.');
    }

    /**
     * Busca el tipo por nombre (Bien o Servicio); si no lo encuentra, usa el primero.
     */
    private function tipoProductoId(string $nombreTipo): int
    {
        return DB::table('tipos_producto')->where('nombre', $nombreTipo)->value('id')
            ?? DB::table('tipos_producto')->orderBy('id')->value('id');
    }
}
