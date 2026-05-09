<?php

namespace App\Models;

use DateTime;

class Producto
{
    public string $idProducto;
    public ?string $idCategoria;
    public ?string $idMedida;
    public string $idMarca;
    public ?string $nombre;
    public ?string $codigo;
    public ?string $sku;
    public string $tipoCodigoBarras;
    public string $codigoBarras;
    public ?string $descripcionCorta;
    public string $descripcionLarga;
    public ?string $idTipoTratamientoProducto;
    public ?string $costo;
    public ?string $idTipoProducto;
    public ?bool $publicar;
    public ?bool $negativo;
    public bool $preferido;
    public ?bool $estado;
    public ?string $imagen;
    public ?DateTime $fecha;
    public ?DateTime $hora;
    public ?DateTime $fupdate;
    public ?DateTime $hupdate;
    public ?string $idUsuario;

    public function __construct(array $data)
    {
        $this->idProducto = $data['idProducto'];
        $this->idCategoria = $data['idCategoria'] ?? null;
        $this->idMedida = $data['idMedida'] ?? null;
        $this->idMarca = $data['idMarca'];
        $this->nombre = $data['nombre'] ?? null;
        $this->codigo = $data['codigo'] ?? null;
        $this->sku = $data['sku'] ?? null;
        $this->tipoCodigoBarras = $data['tipoCodigoBarras'] ?? 'ean13';
        $this->codigoBarras = $data['codigoBarras'];
        $this->descripcionCorta = $data['descripcionCorta'] ?? null;
        $this->descripcionLarga = $data['descripcionLarga'] ?? '';
        $this->idTipoTratamientoProducto = $data['idTipoTratamientoProducto'] ?? null;
        $this->costo = $data['costo'] ?? null;
        $this->idTipoProducto = $data['idTipoProducto'] ?? null;
        $this->publicar = isset($data['publicar']) ? (bool)$data['publicar'] : null;
        $this->negativo = isset($data['negativo']) ? (bool)$data['negativo'] : null;
        $this->preferido = isset($data['preferido']) ? (bool)$data['preferido'] : false;
        $this->estado = isset($data['estado']) ? (bool)$data['estado'] : null;
        $this->imagen = $data['imagen'] ?? null;
        $this->fecha = isset($data['fecha']) ? new DateTime($data['fecha']) : null;
        $this->hora = isset($data['hora']) ? new DateTime($data['hora']) : null;
        $this->fupdate = isset($data['fupdate']) ? new DateTime($data['fupdate']) : null;
        $this->hupdate = isset($data['hupdate']) ? new DateTime($data['hupdate']) : null;
        $this->idUsuario = $data['idUsuario'] ?? null;
    }
}
