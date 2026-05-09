<?php

namespace App\Models;

use DateTime;

class Comprobante
{
    public string $idComprobante;
    public string $idTipoComprobante;
    public string $nombre;
    public string $serie;
    public int $numeracion;
    public string $codigo;
    public string $impresion;
    public bool $estado;
    public bool $preferida;
    public int $numeroCampo;
    public bool $facturado;
    public bool $creditoFiscal;
    public int $anulacion;
    public DateTime $fecha;
    public DateTime $hora;
    public DateTime $fupdate;
    public DateTime $hupdate;
    public string $idUsuario;

    public function __construct(array $data){
        $this->idComprobante = $data['idComprobante'] ?? '';
        $this->idTipoComprobante = $data['idTipoComprobante'] ?? '';
        $this->nombre = $data['nombre'] ?? '';
        $this->serie = $data['serie'] ?? '';
        $this->numeracion = $data['numeracion'] ?? 0;
        $this->codigo = $data['codigo'] ?? '';
        $this->impresion = $data['impresion'] ?? '';
        $this->estado = $data['estado'] ?? false;
        $this->preferida = $data['preferida'] ?? false;
        $this->numeroCampo = $data['numeroCampo'] ?? 0;
        $this->facturado = $data['facturado'] ?? false;
        $this->creditoFiscal = $data['creditoFiscal'] ?? false;
        $this->anulacion = $data['anulacion'] ?? 0;
        $this->fecha = $data['fecha'] ?? date('Y-m-d');
        $this->hora = $data['hora'] ?? date('H:i:s');
        $this->fupdate = $data['fupdate'] ?? null;
        $this->hupdate = $data['hupdate'] ?? null;
        $this->idUsuario = $data['idUsuario'] ?? '';
    }
}
