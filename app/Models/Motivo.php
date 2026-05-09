<?php

namespace App\Models;

use InvalidArgumentException;

class Motivo
{
    public string $idMotivo;
    public string $codigo;
    public string $nombre;
    public string $descripcion;
    public bool $estado;

    public function __construct(array $data)
    {
        $this->idMotivo = $data['idMotivo'] ?? '';
        $this->codigo = $data['codigo'] ?? '';
        $this->nombre = $data['nombre'] ?? '';
        $this->descripcion = $data['descripcion'] ?? '';
        $this->estado = isset($data['estado']) ? (bool)$data['estado'] : true;
    }
}
