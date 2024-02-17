<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class EmpresaRepository
{

    public function obtenerEmpresa()
    {
        $cmd = DB::select("SELECT  
            e.documento,
            td.codigo,
            e.razonSocial,
            e.nombreEmpresa,
            e.usuarioSolSunat,
            e.claveSolSunat,
            e.idApiSunat,
            e.claveApiSunat
        FROM 
            empresa AS e 
        INNER JOIN 
            tipoDocumento AS td ON td.idTipoDocumento = e.idTipoDocumento 
        LIMIT 1");

        if (empty($cmd)) {
            return null;
        }

        return $cmd[0];
    }

    public function get()
    {
        $empresa = DB::select("SELECT 
            idEmpresa,
            documento,
            razonSocial,
            usuarioSolSunat,
            claveSolSunat,
            certificadoSunat,
            claveCertificadoSunat,
            idApiSunat,
            claveApiSunat,
            tipoEnvio
        FROM 
            empresa 
        LIMIT 1");

        return $empresa[0];
    }
}
