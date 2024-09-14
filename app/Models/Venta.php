<?php

namespace App\Models;

class Venta
{
    public string $idVenta;
    public string $comprobante;
    public string $codigoVenta;
    public string $serie;
    public int $numeracion;
    public string $idSucursal;
    public string $tipoDoc;
    public string $codigoCliente;
    public string $documento;
    public string $informacion;
    public string $direccion;
    public string $usuario;
    public string $fecha;
    public string $hora;
    public string $fechaCorrelativo;
    public int $correlativo;
    public string $ticketConsultaSunat;
    public string $idFormaPago;
    public int $estado;
    public string $simbolo;
    public string $codiso;
    public string $moneda;

    public function __construct($data)
    {
        $this->idVenta = $data['idVenta'] ?? "";
        $this->comprobante = $data['comprobante'];
        $this->codigoVenta = $data['codigoVenta'];
        $this->serie = $data['serie'];
        $this->numeracion = $data['numeracion'];
        $this->idSucursal = $data['idSucursal'];
        $this->tipoDoc = $data['tipoDoc'];
        $this->codigoCliente = $data['codigoCliente'];
        $this->documento = $data['documento'];
        $this->informacion = $data['informacion'];
        $this->direccion = $data['direccion']  ?? "";
        $this->usuario = $data['usuario'];
        $this->fecha = $data['fecha'];
        $this->hora = $data['hora'];
        $this->fechaCorrelativo = $data['fechaCorrelativo']  ?? "";
        $this->correlativo = $data['correlativo'] ?? 0;
        $this->ticketConsultaSunat = $data['ticketConsultaSunat']  ?? "";
        $this->idFormaPago = $data['idFormaPago'];
        $this->estado = $data['estado'];
        $this->simbolo = $data['simbolo'];
        $this->codiso = $data['codiso'];
        $this->moneda = $data['moneda'];
    }
}
