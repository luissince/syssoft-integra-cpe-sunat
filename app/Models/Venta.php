<?php

namespace App\Models;

class Venta
{
    public string $idVenta;
    public string $fecha;
    public string $hora;

    public string $codigoComprobante;
    public string $serie;
    public int $numeracion;

    public string $codigoTipoDocumento;
    public string $documento;
    public string $informacion;

    public string $fechaCorrelativo;
    public int $correlativo;
    public string $ticketConsultaSunat;
    public string $idFormaPago;
    public string $fechaVencimiento;

    public string $codiso;
    public string $moneda;

    public function __construct($data)
    {
        $this->idVenta = $data['idVenta'] ?? "";
        $this->fecha = $data['fecha'] ?? "";
        $this->hora = $data['hora'] ?? "";

        $this->codigoComprobante = $data['codigoComprobante'] ?? "";
        $this->serie = $data['serie'] ?? "";
        $this->numeracion = $data['numeracion'] ?? 0;

        $this->codigoTipoDocumento = $data['codigoTipoDocumento'] ?? "";
        $this->documento = $data['documento'] ?? "";
        $this->informacion = $data['informacion'] ?? "";

        $this->fechaCorrelativo = $data['fechaCorrelativo']  ?? "";
        $this->correlativo = $data['correlativo'] ?? 0;
        $this->ticketConsultaSunat = $data['ticketConsultaSunat'] ?? "";
        $this->idFormaPago = $data['idFormaPago'] ?? "";
        $this->fechaVencimiento = $data['fechaVencimiento']  ?? "";

        $this->codiso = $data['codiso'] ?? "";
        $this->moneda = $data['moneda'] ?? "";
    }
}
