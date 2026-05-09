<?php

namespace App\Models;

use DateTime;

class Persona
{
    public string $idPersona;
    public ?string $idTipoDocumento;
    public string $documento;
    public string $informacion;
    public bool $cliente;
    public bool $clientePreferido;
    public bool $proveedor;
    public bool $proveedorPreferido;
    public bool $conductor;
    public bool $conductorPreferido;
    public string $licenciaConducir;
    public int $personal;
    public int $personalPreferido;
    public string $celular;
    public ?string $telefono;
    public ?DateTime $fechaNacimiento;
    public ?string $email;
    public ?string $clave;
    public ?string $genero;
    public string $direccion;
    public ?string $idUbigeo;
    public ?string $estadoCivil;
    public bool $predeterminado;
    public bool $estado;
    public ?string $observacion;
    public DateTime $fecha;
    public DateTime $hora;
    public ?DateTime $fupdate;
    public ?DateTime $hupdate;
    public string $idUsuario;

    public function __construct(array $data) {
        $this->idUsuario = $data['idUsuario'] ?? '';
        $this->idTipoDocumento = $data['idTipoDocumento'] ?? '';
        $this->documento = $data['documento'] ?? '';
        $this->informacion = $data['informacion'] ?? '';
        $this->cliente = $data['cliente'] ?? false;
        $this->clientePreferido = $data['clientePreferido'] ?? false;
        $this->proveedor = $data['proveedor'] ?? false;
        $this->proveedorPreferido = $data['proveedorPreferido'] ?? false;
        $this->conductor = $data['conductor'] ?? false;
        $this->conductorPreferido = $data['conductorPreferido'] ?? false;
        $this->licenciaConducir = $data['licenciaConducir'] ?? '';
        $this->personal = $data['personal'] ?? 0;
        $this->personalPreferido = $data['personalPreferido'] ?? 0;
        $this->celular = $data['celular'] ?? '';
        $this->telefono = $data['telefono'] ?? '';
        $this->fechaNacimiento = $data['fechaNacimiento'] ?? null;
        $this->email = $data['email'] ?? '';
        $this->clave = $data['clave'] ?? '';
        $this->genero = $data['genero'] ?? '';
        $this->direccion = $data['direccion'] ?? '';
        $this->idUbigeo = $data['idUbigeo'] ?? '';
        $this->estadoCivil = $data['estadoCivil'] ?? '';
        $this->predeterminado = $data['predeterminado'] ?? false;
        $this->estado = $data['estado'] ?? 1;
        $this->observacion = $data['observacion'] ?? '';
        $this->fecha = $data['fecha'] ?? date('Y-m-d');
        $this->hora = $data['hora'] ?? date('H:i:s');
        $this->fupdate = $data['fupdate'] ?? null;
        $this->hupdate = $data['hupdate'] ?? null;

    }
}
