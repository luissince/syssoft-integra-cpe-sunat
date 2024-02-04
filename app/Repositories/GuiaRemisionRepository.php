<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class GuiaRemisionRepository
{

    public function obtenerGuiaRemisionPorId(String $idGuiaRemision)
    {
        $cmd = DB::select("SELECT 
        co.codigo,
        gui.serie,
        gui.numeracion,
        gui.fecha,
        gui.hora,
        -- 
        ubp.ubigeo AS ubigeoPartida,
        gui.direccionPartida,
        -- 
        ubl.ubigeo AS ubigeoLlegada,
        gui.direccionLlegada,
        -- 
        mot.codigo AS codigoMotivoTraslado,
        mot.nombre AS nombreMotivoTraslado,
        -- 
        modt.codigo AS codigoModalidadTraslado,
        modt.nombre AS nombreModalidadTraslado,
        -- 
        tp.codigo AS codigoTipoPeso,
        tp.nombre AS nombreTipoPeso, 
        gui.peso,
        -- 
        gui.fechaTraslado,
        -- 
        tdp.codigo AS codigoConductor,
        cod.documento AS documentoConductor,
        cod.informacion AS informacionConductor,
        cod.licenciaConducir,
        -- 
        vh.numeroPlaca,
        -- 
        cpv.codigo AS codigoComprobanteRef,
        cpv.nombre AS nombreComprobanteRef,
        vt.serie AS serieRef,
        vt.numeracion AS numeracionRef,
        -- 
        tdc.codigo AS codDestino,
        cl.documento AS documentoDestino,
        cl.informacion AS informacionDestino,
        -- 
        IFNULL(gui.numeroTicketSunat, '') AS numeroTicketSunat
        FROM guiaRemision AS gui
        INNER JOIN comprobante AS co ON co.idComprobante = gui.idComprobante
        INNER JOIN ubigeo AS ubp ON ubp.idUbigeo = gui.idUbigeoPartida
        INNER JOIN ubigeo AS ubl ON ubl.idUbigeo = gui.idUbigeoLlegada
        INNER JOIN motivoTraslado AS mot ON mot.idMotivoTraslado = gui.idMotivoTraslado
        INNER JOIN modalidadTraslado AS modt ON modt.idModalidadTraslado = gui.idModalidadTraslado
        INNER JOIN tipoPeso AS tp ON tp.idTipoPeso = gui.idTipoPeso
        INNER JOIN persona AS cod ON cod.idPersona = gui.idConductor
        INNER JOIN tipoDocumento AS tdp ON tdp.idTipoDocumento = cod.idTipoDocumento
        INNER JOIN vehiculo AS vh ON vh.idVehiculo = gui.idVehiculo
        INNER JOIN venta AS vt ON vt.idVenta = gui.idVenta
        INNER JOIN comprobante AS cpv ON cpv.idComprobante = vt.idComprobante
        INNER JOIN persona AS cl ON cl.idPersona = vt.idCliente
        INNER JOIN tipoDocumento AS tdc ON  tdc.idTipoDocumento = cl.idTipoDocumento
        WHERE gui.idGuiaRemision = ?", [
            $idGuiaRemision
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

    public function obtenerDetalleGuiaRemisionPorId(string $idGuiaRemision)
    {
        return DB::select("SELECT 
        p.idProducto,
        p.nombre,
        gd.cantidad,
        m.codigo codigoMedida
        FROM guiaRemisionDetalle AS gd
        INNER JOIN producto AS p ON gd.idProducto = p.idProducto
        INNER JOIN medida AS m ON m.idMedida = p.idMedida
        WHERE gd.idGuiaRemision = ?", [
            $idGuiaRemision
        ]);
    }
}
