<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SucursalRepository
{

    public function obtenerSucursalPorId(String $idSucursal)
    {
        $cmd = DB::select("SELECT 
                s.direccion,
                u.ubigeo,
                u.departamento,
                u.provincia,
                u.distrito
            FROM 
                sucursal AS s 
            INNER JOIN 
                ubigeo AS u ON u.idUbigeo = s.idUbigeo
            WHERE 
                s.idSucursal = ?", [
            $idSucursal
        ]);

        if (empty($cmd)) {
            return null;
        }

        return $cmd[0];
    }
}
