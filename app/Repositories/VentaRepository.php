<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class VentaRepository
{

    public function obtenerVentaPorId(String $idVenta)
    {
        $cmd = DB::select("SELECT
            v.idVenta, 
            com.nombre AS comprobante,
            com.codigo as codigoVenta,
            v.serie,
            v.numeracion,
            v.idSucursal,
            td.nombre AS tipoDoc,      
            td.codigo AS codigoCliente,      
            c.documento,
            c.informacion,
            c.direccion,
            CONCAT(us.nombres,' ',us.apellidos) AS usuario,
            v.fecha,
            v.hora, 
            v.fechaCorrelativo,
            v.correlativo,
            IFNULL(v.ticketConsultaSunat,'') AS ticketConsultaSunat,
            tv.nombre AS formaVenta, 
            v.estado, 
            m.simbolo,
            m.codiso,
            m.nombre as moneda
        FROM venta AS v 
            INNER JOIN clienteNatural AS c ON v.idCliente = c.idCliente
            INNER JOIN usuario AS us ON us.idUsuario = v.idUsuario 
            INNER JOIN tipoDocumento AS td ON td.idTipoDocumento = c.idTipoDocumento 
            INNER JOIN comprobante AS com ON v.idComprobante = com.idComprobante
            INNER JOIN moneda AS m ON m.idMoneda = v.idMoneda
            INNER JOIN formaVenta  AS tv ON tv.idFormaVenta = v.idFormaVenta
        WHERE 
            v.idVenta = ?", [
            $idVenta
        ]);

        if (empty($cmd)) {
            return null;
        }

        return $cmd[0];
    }

    public function obteneCorrelativoResumenDiario()
    {
        // $data = DB::table('venta')->where('idVenta', 'VT0001')->first(["fecha", "hora"]);
        $correlativo = DB::table('venta')
            ->whereDate('fechaCorrelativo', now()->toDateString())
            ->max('correlativo');

        return $correlativo = $correlativo ?? 0;
    }

    public function obtenerDetalleVentaPorId(string $idVenta)
    {
        return DB::select("SELECT 
            p.nombre AS producto,
            md.codigo AS codigoMedida,
            md.nombre AS medida, 
            m.nombre AS categoria, 
            vd.precio,
            vd.cantidad,
            vd.idImpuesto,
            imp.nombre AS impuesto,
            imp.codigo,
            imp.porcentaje
        FROM ventaDetalle AS vd 
            INNER JOIN producto AS p ON vd.idProducto = p.idProducto 
            INNER JOIN medida AS md ON md.idMedida = p.idMedida 
            INNER JOIN categoria AS m ON p.idCategoria = m.idCategoria 
            INNER JOIN impuesto AS imp ON vd.idImpuesto  = imp.idImpuesto  
        WHERE vd.idVenta = ?", [
            $idVenta
        ]);
    }
}
