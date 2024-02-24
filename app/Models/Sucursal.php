<?php

namespace App\Models;

class Sucursal
{
	public string $direccion;
	public string $ubigeo;
	public string $departamento;
	public string $provincia;
	public string $distrito;

	public function __construct($data)
    {
        $this->direccion = $data['direccion'];
        $this->ubigeo = $data['ubigeo'];
        $this->departamento = $data['departamento'];
        $this->provincia = $data['provincia'];
        $this->distrito = $data['distrito'];
    }
}