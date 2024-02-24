<?php

namespace App\Models;

class Empresa
{
	public string $documento;
	public string $codigo;
	public string $razonSocial;
	public string $nombreEmpresa;
	public string $usuarioSolSunat;
	public string $claveSolSunat;
	public string $idApiSunat;
	public string $claveApiSunat;

	public function __construct($data)
    {
        $this->documento = $data['documento'];
        $this->codigo = $data['codigo'];
        $this->razonSocial = $data['razonSocial'];
        $this->nombreEmpresa = $data['nombreEmpresa'];
        $this->usuarioSolSunat = $data['usuarioSolSunat'];
        $this->claveSolSunat = $data['claveSolSunat'];
        $this->idApiSunat = $data['idApiSunat'];
        $this->claveApiSunat = $data['claveApiSunat'];
    }
}
