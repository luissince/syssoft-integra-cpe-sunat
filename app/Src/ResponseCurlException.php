<?php
namespace App\Src;

use Exception;

class ResponseCurlException extends Exception
{
    private $codigo;
    private $mensaje;

    public function __construct($codigo, $mensaje, $code = 0, Exception $previous = null) {
        $this->codigo = $codigo;
        $this->mensaje = $mensaje;

        $message = "Error al obtener el token - Código: $codigo, Mensaje: $mensaje";
        parent::__construct($message, $code, $previous);
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getMensaje() {
        return $this->mensaje;
    }
}