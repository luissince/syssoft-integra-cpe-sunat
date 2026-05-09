<?php

namespace App\Models;

use DateTime;
use InvalidArgumentException;

class Medida
{
    public string $idMedida;
    public string $codigo;
    public string $nombre;
    public string $descripcion;
    public bool $estado;
    public bool $preferida;
    public DateTime $fecha;
    public DateTime $hora;
    public string $idUsuario;

    public function __construct(array $data)
    {
        if (!isset($data['idMedida'])) {
            throw new InvalidArgumentException('idMedida es obligatorio');
        }

        $this->idMedida = $data['idMedida'];
        $this->codigo = $data['codigo'] ?? '';
        $this->nombre = $data['nombre'] ?? '';
        $this->descripcion = $data['descripcion'] ?? '';
        $this->estado = isset($data['estado']) ? (bool)$data['estado'] : true;
        $this->preferida = isset($data['preferida']) ? (bool)$data['preferida'] : false;
        $this->fecha = isset($data['fecha']) ? new DateTime($data['fecha']) : new DateTime();
        $this->hora = isset($data['hora']) ? new DateTime($data['hora']) : new DateTime();
        $this->idUsuario = $data['idUsuario'] ?? '';
    }

    public function estaActiva(): bool
    {
        return $this->estado;
    }

    public function esPreferida(): bool
    {
        return $this->preferida;
    }
}
