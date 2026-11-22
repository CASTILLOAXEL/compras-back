<?php

namespace App\Services\Services;

use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Validation\ValidationException;


class SolicitudService
{
    private const PREFIJO_NUMERO = 'SOL-';

    public function crear(int $centroCostoId, Usuario $solicitante): Solicitud
    {
        $solicitud = Solicitud::create([
            'numero' => $this->siguienteNumero(),
            'fecha' => today(),
            'solicitante_id' => $solicitante->id,
            'centro_costo_id' => $centroCostoId,
            'estado_solicitud_id' => EstadoSolicitud::BORRADOR,
        ]);

        $this->registrarHistorial($solicitud, $solicitante, 'Solicitud creada.');

        return $solicitud;
    }

    public function enviarAAprobacion(Solicitud $solicitud, Usuario $usuario): Solicitud
    {
        $this->validarEstado($solicitud, EstadoSolicitud::BORRADOR, 'Solo se pueden enviar solicitudes en borrador.');

        if (! $solicitud->detalles()->exists()) {
            throw ValidationException::withMessages([
                'detalles' => 'La solicitud debe tener al menos un ítem para enviarla a aprobación.',
            ]);
        }

        return $this->cambiarEstado($solicitud, EstadoSolicitud::EN_APROBACION, $usuario, 'Enviada a aprobación.');
    }

    public function aprobar(Solicitud $solicitud, Usuario $aprobador, ?string $observacion): Solicitud
    {
        $this->validarEstado($solicitud, EstadoSolicitud::EN_APROBACION, 'Solo se pueden aprobar solicitudes en aprobación.');

        return $this->cambiarEstado($solicitud, EstadoSolicitud::APROBADA, $aprobador, $observacion ?? 'Solicitud aprobada.');
    }

    public function completar(Solicitud $solicitud, Usuario $usuario, string $observacion): Solicitud
    {
        $this->validarEstado($solicitud, EstadoSolicitud::APROBADA, 'Solo una solicitud aprobada puede completarse.');

        return $this->cambiarEstado($solicitud, EstadoSolicitud::COMPLETADA, $usuario, $observacion);
    }

    public function rechazar(Solicitud $solicitud, Usuario $aprobador, string $motivo): Solicitud
    {
        $this->validarEstado($solicitud, EstadoSolicitud::EN_APROBACION, 'Solo se pueden rechazar solicitudes en aprobación.');

        return $this->cambiarEstado($solicitud, EstadoSolicitud::RECHAZADA, $aprobador, $motivo);
    }

    public function validarEstado(Solicitud $solicitud, int $estadoEsperadoId, string $mensaje): void
    {
        if (! $solicitud->estaEnEstado($estadoEsperadoId)) {
            throw ValidationException::withMessages(['estado' => $mensaje]);
        }
    }

    private function cambiarEstado(Solicitud $solicitud, int $nuevoEstadoId, Usuario $usuario, string $observacion): Solicitud
    {
        $solicitud->update(['estado_solicitud_id' => $nuevoEstadoId]);

        $this->registrarHistorial($solicitud, $usuario, $observacion);

        return $solicitud;
    }

    private function registrarHistorial(Solicitud $solicitud, Usuario $usuario, string $observacion): void
    {
        $solicitud->historial()->create([
            'estado_solicitud_id' => $solicitud->estado_solicitud_id,
            'usuario_id' => $usuario->id,
            'observacion' => $observacion,
        ]);
    }

    private function siguienteNumero(): string
    {
        $ultimoId = Solicitud::withTrashed()->lockForUpdate()->max('id') ?? 0;

        return self::PREFIJO_NUMERO . str_pad($ultimoId + 1, 6, '0', STR_PAD_LEFT);
    }
}
