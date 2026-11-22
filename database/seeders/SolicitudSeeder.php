<?php

namespace Database\Seeders;

use App\Models\CentroCosto;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Solicitud;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\Services\OrdenService;
use App\Services\Services\RecepcionService;
use App\Services\Services\SolicitudService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Solicitudes de prueba en todos sus estados, con sus órdenes y recepciones.
 * Usa los mismos servicios que la API: número automático, historial y reglas del negocio.
 * Requiere los usuarios de prueba y ProveedorSeeder.
 */
class SolicitudSeeder extends Seeder
{
    private SolicitudService $solicitudService;
    private OrdenService $ordenService;
    private RecepcionService $recepcionService;

    private Usuario $administrador;
    private Usuario $solicitante;

    private array $productosIds = [];
    private array $proveedoresIds = [];
    private int $unidadMedidaId;

    public function run(SolicitudService $solicitudService, OrdenService $ordenService, RecepcionService $recepcionService): void
    {
        $this->solicitudService = $solicitudService;
        $this->ordenService = $ordenService;
        $this->recepcionService = $recepcionService;

        $this->administrador = Usuario::where('correo', 'admin@compras.test')->firstOrFail();
        $this->solicitante = Usuario::where('correo', 'solicitante@compras.test')->firstOrFail();

        $this->productosIds = Producto::where('estado', true)->orderBy('id')->pluck('id')->all();
        $this->proveedoresIds = Proveedor::where('estado', true)->orderBy('id')->pluck('id')->all();
        $this->unidadMedidaId = UnidadMedida::orderBy('id')->value('id');

        if (! $this->proveedoresIds || ! $this->productosIds) {
            $this->command->warn('Faltan proveedores o productos: corra antes ProveedorSeeder y ProductoSeeder.');

            return;
        }

        // Todo o nada: si algo falla, no queda ningún dato a medias
        DB::transaction(function () {
            // 1 y 2. Borrador
            $this->crearSolicitud($this->solicitante, 'Tecnología', [
                ['producto' => 0, 'cantidad' => 3, 'precio' => 4500],
                ['producto' => 1, 'cantidad' => 5, 'precio' => 350],
            ]);

            $this->crearSolicitud($this->administrador, 'Agencias', [
                ['producto' => 2, 'cantidad' => 10, 'precio' => 125.50],
            ]);

            // 3. En aprobación
            $solicitudEnAprobacion = $this->crearSolicitud($this->solicitante, 'Recursos Humanos', [
                ['producto' => 1, 'cantidad' => 20, 'precio' => 45],
                ['producto' => 2, 'cantidad' => 2, 'precio' => 1800],
            ]);
            $this->solicitudService->enviarAAprobacion($solicitudEnAprobacion, $this->solicitante);

            // 4. Aprobada: queda lista para generar su orden desde la pantalla
            $this->crearEnviarYAprobar($this->solicitante, 'Créditos', [
                ['producto' => 0, 'cantidad' => 2, 'precio' => 7500],
                ['producto' => 2, 'cantidad' => 4, 'precio' => 950],
            ]);

            // 5. Rechazada
            $solicitudRechazada = $this->crearSolicitud($this->solicitante, 'Gerencia General', [
                ['producto' => 0, 'cantidad' => 6, 'precio' => 8200],
            ]);
            $this->solicitudService->enviarAAprobacion($solicitudRechazada, $this->solicitante);
            $this->solicitudService->rechazar(
                $solicitudRechazada,
                $this->administrador,
                'Supera el presupuesto del trimestre; reducir la cantidad.'
            );

            // 6. Completada: orden emitida con una recepción parcial
            $ordenConRecepcionParcial = $this->generarOrden(
                $this->crearEnviarYAprobar($this->solicitante, 'Tecnología', [
                    ['producto' => 0, 'cantidad' => 5, 'precio' => 6800],
                    ['producto' => 1, 'cantidad' => 10, 'precio' => 300],
                ]),
                posicionProveedor: 0
            );
            $this->registrarRecepcion($ordenConRecepcionParcial, recibirTodo: false, observaciones: 'Llegaron 2 equipos; el resto la próxima semana.');

            // 7. Completada: orden recibida en su totalidad
            $ordenRecibida = $this->generarOrden(
                $this->crearEnviarYAprobar($this->administrador, 'Agencias', [
                    ['producto' => 1, 'cantidad' => 50, 'precio' => 40],
                    ['producto' => 2, 'cantidad' => 8, 'precio' => 210],
                ]),
                posicionProveedor: 1
            );
            $this->registrarRecepcion($ordenRecibida, recibirTodo: true, observaciones: 'Entrega completa y en buen estado.');

            // 8. Completada: orden cancelada
            $ordenCancelada = $this->generarOrden(
                $this->crearEnviarYAprobar($this->administrador, 'Recursos Humanos', [
                    ['producto' => 2, 'cantidad' => 3, 'precio' => 1500],
                ]),
                posicionProveedor: 2
            );
            $this->ordenService->cancelar($ordenCancelada);
        });

        $this->command->info('Solicitudes, órdenes y recepciones de prueba creadas.');
    }

    /**
     * Crea la solicitud en Borrador con sus ítems.
     *
     * @param  array<int, array{producto: int, cantidad: float, precio: float}>  $items
     */
    private function crearSolicitud(Usuario $usuario, string $nombreCentroCosto, array $items): Solicitud
    {
        $solicitud = $this->solicitudService->crear($this->centroCostoId($nombreCentroCosto), $usuario);

        foreach ($items as $item) {
            $solicitud->detalles()->create([
                'producto_id' => $this->productoId($item['producto']),
                'unidad_medida_id' => $this->unidadMedidaId,
                'cantidad' => $item['cantidad'],
                'precio_estimado' => $item['precio'],
            ]);
        }

        return $solicitud;
    }

    private function crearEnviarYAprobar(Usuario $usuario, string $nombreCentroCosto, array $items): Solicitud
    {
        $solicitud = $this->crearSolicitud($usuario, $nombreCentroCosto, $items);

        $this->solicitudService->enviarAAprobacion($solicitud, $usuario);
        $this->solicitudService->aprobar($solicitud, $this->administrador, 'Presupuesto disponible.');

        return $solicitud;
    }

    /**
     * Genera la orden con todos los ítems; el precio real queda 5 % abajo del estimado.
     */
    private function generarOrden(Solicitud $solicitud, int $posicionProveedor): Orden
    {
        $items = $solicitud->detalles()->orderBy('id')->get()
            ->map(fn ($solicitudDetalle) => [
                'solicitud_detalle_id' => $solicitudDetalle->id,
                'cantidad' => (float) $solicitudDetalle->cantidad,
                'precio_unitario' => round((float) $solicitudDetalle->precio_estimado * 0.95, 2),
            ])
            ->all();

        $proveedorId = $this->proveedoresIds[$posicionProveedor % count($this->proveedoresIds)];

        return $this->ordenService->generarDesdeSolicitud($solicitud, $proveedorId, $items, $this->administrador);
    }

    /**
     * Recibe todo lo pedido, o solo una parte del primer ítem (recepción parcial).
     */
    private function registrarRecepcion(Orden $orden, bool $recibirTodo, string $observaciones): void
    {
        $detallesOrden = $orden->detalles()->orderBy('id')->get();

        $items = $recibirTodo
            ? $detallesOrden->map(fn ($ordenDetalle) => [
                'orden_detalle_id' => $ordenDetalle->id,
                'cantidad_recibida' => (float) $ordenDetalle->cantidad,
            ])->all()
            : [[
                'orden_detalle_id' => $detallesOrden->first()->id,
                'cantidad_recibida' => 2,
            ]];

        $this->recepcionService->registrar($orden, [
            'fecha' => now()->toDateString(),
            'observaciones' => $observaciones,
            'items' => $items,
        ], $this->administrador);
    }

    private function centroCostoId(string $nombreCentroCosto): int
    {
        return CentroCosto::where('nombre', $nombreCentroCosto)->value('id')
            ?? CentroCosto::orderBy('id')->value('id');
    }

    // Si hay pocos productos, vuelve a empezar desde el primero
    private function productoId(int $posicion): int
    {
        return $this->productosIds[$posicion % count($this->productosIds)];
    }
}
