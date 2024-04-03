<?php

namespace App\Models\GuiaRemision;

class Detalle
{
	public string $idProducto;
	public string $nombre;
	public float $cantidad;
	public string $codigoMedida;

    public function __construct(array $data)
    {
        $this->idProducto = $data['idProducto'] ?? '';
        $this->nombre = $data['nombre'] ?? '';
        $this->cantidad = $data['cantidad'] ?? 0;
        $this->codigoMedida = $data['codigoMedida'] ?? '';
    }
}