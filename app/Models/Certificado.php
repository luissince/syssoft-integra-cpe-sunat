<?php

namespace App\Models;

class Certificado
{
    public string $privateKey;
    public string $publicKey;

    public function __construct($data)
    {
        $this->privateKey = $data['privateKey'];
        $this->publicKey = $data['publicKey'];
    }
}
