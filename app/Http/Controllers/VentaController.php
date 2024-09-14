<?php

namespace App\Http\Controllers;

use App\Helpers\SunatHelper;
use App\Helpers\XmlGenerator;
use App\Models\Certificado;
use App\Models\Detalle;
use App\Models\Empresa;
use App\Models\Cuota;
use App\Models\Sucursal;
use App\Models\Venta;
use DateTime;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function __construct() {}

    public function sendBoletaOrFactura(Request $request)
    {
        $venta = new Venta($request->venta);
        $empresa = new Empresa($request->empresa);
        $sucursal = new Sucursal($request->sucursal);
        $certificado = new Certificado($request->certificado);

        $detalles = [];
        foreach ($request->detalle as $detalleData) {
            $detalle = new Detalle($detalleData);
            $detalles[] = $detalle;
        }

        $cuotas = [];
        foreach ($request->cuotas as $cuota) {
            $cuota = new Cuota($cuota);
            $cuotas[] = $cuota;
        }

        $xml = XmlGenerator::createInvoiceXml($venta, $detalles, $cuotas, $empresa, $sucursal);

        $fileName = $empresa->documento . '-' . $venta->codigoVenta . '-' . $venta->serie . '-' . $venta->numeracion;

        return SunatHelper::sendBill($fileName, $xml, $empresa, $certificado);
    }

    public function sendResumenDiario(Request $request)
    {
        $venta = new Venta($request->venta);
        $empresa = new Empresa($request->empresa);
        $certificado = new Certificado($request->certificado);
        $correlativoActual = $request->correlativoActual;

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($venta->ticketConsultaSunat != '') {
            return SunatHelper::getStatus($venta, $empresa, $fileName);
        }

        $detalles = [];
        foreach ($request->detalle as $detalleData) {
            $detalle = new Detalle($detalleData);
            $detalles[] = $detalle;
        }

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::createSummaryDocumentsXml($venta, $detalles, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumary($fileName, $xml, $empresa, $certificado, $correlativo, $currentDate);
    }

    public function sendComunicacionDeBaja(Request $request)
    {
        $venta = new Venta($request->venta);
        $empresa = new Empresa($request->empresa);
        $certificado = new Certificado($request->certificado);
        $correlativoActual = $request->correlativoActual;

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($venta->ticketConsultaSunat != '') {
            return SunatHelper::getStatus($venta, $empresa, $fileName);
        }

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::generateVoidedDocumentsXml($venta, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumary($fileName, $xml,  $empresa, $certificado, $correlativo, $currentDate);
    }
}
