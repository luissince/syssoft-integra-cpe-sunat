<?php

namespace App\Models;

namespace App\Models;

use App\Enums\TipoEntidad;

class TipoDocumento
{
    public string $idTipoDocumento;
    public string $nombre;
    public string $descripcion;
    public int $longitud;
    public bool $obligado;
    public string $codigo;
    public bool $estado;
    public TipoEntidad $tipoEntidad;

    public function __construct(array $data)
    {
        $this->idTipoDocumento = $data['idTipoDocumento'] ?? '';
        $this->nombre = $data['nombre'] ?? '';
        $this->descripcion = $data['descripcion'] ?? '';
        $this->longitud = $data['longitud'] ?? 0;
        $this->obligado = $data['obligado'] ?? false;
        $this->codigo = $data['codigo'] ?? '';
        $this->estado = $data['estado'] ?? false;
        $this->tipoEntidad = $data['tipoEntidad'] ?? TipoEntidad::NATURAL;
    }
}
