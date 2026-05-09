<?php

namespace App\Models;

use DateTime;
use InvalidArgumentException;

class Impuesto
{
    public string $idImpuesto;
    public string $nombre;
    public int $porcentaje;
    public string $codigo;
    public bool $estado;
    public bool $preferido;
    public DateTime $fecha;
    public DateTime $hora;
    public DateTime $fupdate;
    public DateTime $hupdate;
    public string $idUsuario;

    public function __construct(array $data)
    {
        if (!isset($data['idImpuesto'])) {
            throw new InvalidArgumentException('idImpuesto es obligatorio');
        }

        $this->idImpuesto = $data['idImpuesto'];
        $this->nombre = $data['nombre'] ?? '';
        $this->porcentaje = isset($data['porcentaje']) ? (int)$data['porcentaje'] : 0;
        $this->codigo = $data['codigo'] ?? '';
        $this->estado = isset($data['estado']) ? (bool)$data['estado'] : true;
        $this->preferido = isset($data['preferido']) ? (bool)$data['preferido'] : false;
        $this->fecha = isset($data['fecha']) ? new DateTime($data['fecha']) : new DateTime();
        $this->hora = isset($data['hora']) ? new DateTime($data['hora']) : new DateTime();
        $this->fupdate = isset($data['fupdate']) ? new DateTime($data['fupdate']) : new DateTime();
        $this->hupdate = isset($data['hupdate']) ? new DateTime($data['hupdate']) : new DateTime();
        $this->idUsuario = $data['idUsuario'] ?? '';
    }

    public function calcularMonto(float $base): float
    {
        return ($base * $this->porcentaje) / 100;
    }

    public function estaActivo(): bool
    {
        return $this->estado;
    }
}
