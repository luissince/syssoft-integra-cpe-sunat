<?php

namespace App\Models;

class Detalle
{
    public string $producto;
    public string $codigoMedida;
    public string $medida;
    public string $categoria;
    public int $precio;
    public int $cantidad;
    public string $idImpuesto;
    public string $impuesto;
    public string $codigo;
    public int $porcentaje;

    public function __construct($data)
    {
        $this->producto = $data['producto'];
        $this->codigoMedida = $data['codigoMedida'];
        $this->medida = $data['medida'];
        $this->categoria = $data['categoria'];
        $this->precio = $data['precio'];
        $this->cantidad = $data['cantidad'];
        $this->idImpuesto = $data['idImpuesto'];
        $this->impuesto = $data['impuesto'];
        $this->codigo = $data['codigo'];
        $this->porcentaje = $data['porcentaje'];
    }
}
