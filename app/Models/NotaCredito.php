<?php

namespace App\Models;

class NotaCredito
{
    public string $idNotaCredito;
    public string $fecha;
    public string $hora;

    public string $codigoComprobante;

    public string $serie;
    public int $numeracion;

    public string $codigoTipoDocumento;

    public string $documento;
    public string $informacion;

    public string $codigoMotivo;
    public string $motivoAnulacion;

    public string $codiso;
    public string $moneda;

    public string $codigoComprobanteVenta;
    public string $serieVenta;
    public int $numeracionVenta;

    public string $fechaCorrelativo;
    public int $correlativo;
    public string $ticketConsultaSunat;

    public function __construct(array $data)
    {
        $this->idNotaCredito = $data['idNotaCredito'] ?? "";
        $this->fecha = $data['fecha'] ?? "";
        $this->hora = $data['hora'] ?? "";

        $this->codigoComprobante = $data['codigoComprobante'] ;
        $this->serie = $data['serie'] ?? "";
        $this->numeracion = $data['numeracion'] ?? 0;

        $this->codigoTipoDocumento = $data['codigoTipoDocumento'] ?? "";
        $this->documento = $data['documento'] ?? "";
        $this->informacion = $data['informacion'] ?? "";

        $this->codigoMotivo = $data['codigoMotivo'] ?? "";
        $this->motivoAnulacion = $data['motivoAnulacion'] ?? "";

        $this->codiso = $data['codiso'] ?? "";
        $this->moneda = $data['moneda'] ?? "";

        $this->codigoComprobanteVenta = $data['codigoComprobanteVenta'] ?? "";
        $this->serieVenta = $data['serieVenta'] ?? "";
        $this->numeracionVenta = $data['numeracionVenta'] ?? 0;

        $this->fechaCorrelativo = $data['fechaCorrelativo'] ?? "";
        $this->correlativo = (int)$data['correlativo'] ?? 0;
        $this->ticketConsultaSunat = $data['ticketConsultaSunat'] ?? "";
    }
}
