<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class ResponseCurlException extends Exception
{
    private string $codigo;
    private string $mensaje;

    public function __construct(
        string $codigo,
        string $mensaje,
        int $code = 0,
        ?Throwable $previous = null
    ) {

        $this->codigo = $codigo;
        $this->mensaje = $mensaje;

        parent::__construct($mensaje, $code, $previous);
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getMensaje(): string
    {
        return $this->mensaje;
    }
}