<?php

namespace App\Models;

class Plazo
{
    public int $idPlazo;
    public string $idVenta;
    public int $cuota;
    public string $fecha;
    public string $hora;
    public float $monto;
    public bool $estado;

    public function __construct($data)
    {
        $this->idPlazo = $data['idPlazo'] ?? 0;
        $this->idVenta = $data['idVenta'] ?? '';
        $this->cuota = $data['cuota'] ?? 0;
        $this->fecha = $data['fecha'] ?? '';
        $this->hora = $data['hora'] ?? '';
        $this->monto = $data['monto'] ?? 0.00;
        $this->estado = $data['estado'] ?? false;
    }
}
