<?php

namespace App\Models;

class Consulta
{
    public string $ruc;
    public string $usuarioSol;
    public string $claveSol;
    public string $tipoComprobante;
    public string $serie;
    public int $numeracion;

    public function __construct($data)
    {
        $this->ruc = $data["ruc"];
        $this->usuarioSol = $data["usuarioSol"];
        $this->claveSol = $data["claveSol"];
        $this->tipoComprobante = $data["tipoComprobante"];
        $this->serie = $data["serie"];
        $this->numeracion = $data["numeracion"];
    }
}