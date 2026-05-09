<?php

namespace App\Models\GuiaRemision;

class GuiaRemision
{
    public string $idGuiaRemision;
    public string $codigo;
    public string $serie;
    public int $numeracion;
    public string $fecha;
    public string $hora;
    public string $ubigeoPartida;
    public string $direccionPartida;
    public string $ubigeoLlegada;
    public string $direccionLlegada;
    public string $codigoMotivoTraslado;
    public string $nombreMotivoTraslado;
    public string $codigoModalidadTraslado;
    public string $nombreModalidadTraslado;
    public string $codigoTipoPeso;
    public string $nombreTipoPeso;
    public float $peso;
    public string $fechaTraslado;
    public string $codigoConductor;
    public string $documentoConductor;
    public string $informacionConductor;
    public string $licenciaConducir;
    public string $numeroPlaca;
    public string $codigoComprobanteRef;
    public string $nombreComprobanteRef;
    public string $serieRef;
    public int $numeracionRef;
    public string $codDestino;
    public string $documentoDestino;
    public string $informacionDestino;
    public string $numeroTicketSunat;

    public function __construct(array $data)
    {   
        $this->idGuiaRemision = $data['idGuiaRemision'];
        $this->codigo = $data['codigo'] ?? '';
        $this->serie = $data['serie'] ?? '';
        $this->numeracion = $data['numeracion'] ?? 0;
        $this->fecha = $data['fecha'] ?? '';
        $this->hora = $data['hora'] ?? '';
        $this->ubigeoPartida = $data['ubigeoPartida'] ?? '';
        $this->direccionPartida = $data['direccionPartida'] ?? '';
        $this->ubigeoLlegada = $data['ubigeoLlegada'] ?? '';
        $this->direccionLlegada = $data['direccionLlegada'] ?? '';
        $this->codigoMotivoTraslado = $data['codigoMotivoTraslado'] ?? '';
        $this->nombreMotivoTraslado = $data['nombreMotivoTraslado'] ?? '';
        $this->codigoModalidadTraslado = $data['codigoModalidadTraslado'] ?? '';
        $this->nombreModalidadTraslado = $data['nombreModalidadTraslado'] ?? '';
        $this->codigoTipoPeso = $data['codigoTipoPeso'] ?? '';
        $this->nombreTipoPeso = $data['nombreTipoPeso'] ?? '';
        $this->peso = $data['peso'] ?? 0;
        $this->fechaTraslado = $data['fechaTraslado'] ?? '';
        $this->codigoConductor = $data['codigoConductor'] ?? '';
        $this->documentoConductor = $data['documentoConductor'] ?? '';
        $this->informacionConductor = $data['informacionConductor'] ?? '';
        $this->licenciaConducir = $data['licenciaConducir'] ?? '';
        $this->numeroPlaca = $data['numeroPlaca'] ?? '';
        $this->codigoComprobanteRef = $data['codigoComprobanteRef'] ?? '';
        $this->nombreComprobanteRef = $data['nombreComprobanteRef'] ?? '';
        $this->serieRef = $data['serieRef'] ?? '';
        $this->numeracionRef = $data['numeracionRef'] ?? 0;
        $this->codDestino = $data['codDestino'] ?? '';
        $this->documentoDestino = $data['documentoDestino'] ?? '';
        $this->informacionDestino = $data['informacionDestino'] ?? '';
        $this->numeroTicketSunat = $data['numeroTicketSunat'] ?? '';
    }
}
