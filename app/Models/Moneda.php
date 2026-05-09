<?php

namespace App\Models;

use DateTime;

class Moneda
{
    public string $idMoneda;
    public string $nombre;
    public string $codiso;
    public string $simbolo;
    public bool $estado;
    public bool $nacional;
    public DateTime $fecha;
    public DateTime $hora;
    public DateTime $fupdate;
    public DateTime $hupdate;
    public string $idUsuario;

    public function __construct(array $data) {
        $this->idMoneda = $data['idMoneda'] ?? '';
        $this->nombre = $data['nombre'] ?? '';
        $this->codiso = $data['codiso'] ?? '';
        $this->simbolo = $data['simbolo'] ?? '';
        $this->estado = $data['estado'] ?? false;
        $this->nacional = $data['nacional'] ?? false;
        $this->fecha = $data['fecha'] ?? date('Y-m-d');
        $this->hora = $data['hora'] ?? date('H:i:s');
        $this->fupdate = $data['fupdate'] ?? null;
        $this->hupdate = $data['hupdate'] ?? null;
        $this->idUsuario = $data['idUsuario'] ?? '';
    }
}
