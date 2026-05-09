<?php

namespace App\Models;

class NotaCreditoDetalle
{
    public string $producto;
    public string $codigoMedida;
    public string $medida;
    public string $categoria;

    public float $precio;
    public float $cantidad;

    public string $idImpuesto;
    public string $impuesto;
    public string $codigo;
    public float $porcentaje;

    public function __construct(array $data)
    {
        $this->producto = $data['producto'];
        $this->codigoMedida = $data['codigoMedida'];
        $this->medida = $data['medida'];
        $this->categoria = $data['categoria'];

        $this->precio = (float)$data['precio'];
        $this->cantidad = (float)$data['cantidad'];

        $this->idImpuesto = $data['idImpuesto'];
        $this->impuesto = $data['impuesto'];
        $this->codigo = $data['codigo'];
        $this->porcentaje = (float)$data['porcentaje'];
    }
}
