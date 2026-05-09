<?php

namespace App\Http\Controllers;

use App\Helpers\SunatHelper;
use App\Helpers\XmlGenerator;
use App\Models\Certificado;
use App\Models\Empresa;
use App\Models\NotaCredito;
use App\Models\NotaCreditoDetalle;
use App\Models\Sucursal;
use DateTime;
use Illuminate\Http\Request;

class NotaDeCreditoController extends Controller
{

    public function sendNotaDeCredito(Request $request)
    {
        $notaCredito = new NotaCredito($request->notaCredito);
        $empresa = new Empresa($request->empresa);
        $sucursal = new Sucursal($request->sucursal);
        $certificado = new Certificado($request->certificado);

        $detalles = [];
        foreach ($request->detalles as $detalleData) {
            $detalle = new NotaCreditoDetalle($detalleData);
            $detalles[] = $detalle;
        }

        $xml = XmlGenerator::createCreditoNoteXml($notaCredito, $detalles, $empresa, $sucursal);

        $fileName = $empresa->documento . '-' . $notaCredito->codigoComprobante . '-' . $notaCredito->serie . '-' . $notaCredito->numeracion;

        return SunatHelper::sendBill($fileName, $xml, $empresa, $certificado);
    }

    public function sendResumenDiario(Request $request)
    {
        $notaCredito = new NotaCredito($request->notaCredito);
        $empresa = new Empresa($request->empresa);
        $certificado = new Certificado($request->certificado);
        $correlativoActual = $request->correlativoActual;

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($notaCredito->ticketConsultaSunat != '') {
            return SunatHelper::getStatus($notaCredito->ticketConsultaSunat, $empresa, $fileName);
        }

        $detalles = [];
        foreach ($request->detalles as $detalleData) {
            $detalle = new NotaCreditoDetalle($detalleData);
            $detalles[] = $detalle;
        }

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::createSummaryCreditNoteXml($notaCredito, $detalles, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumary($fileName, $xml, $empresa, $certificado, $correlativo, $currentDate);
    }

    public function sendComunicacionDeBaja(Request $request)
    {
        $notaCredito = new NotaCredito($request->notaCredito);
        $empresa = new Empresa($request->empresa);
        $certificado = new Certificado($request->certificado);
        $correlativoActual = $request->correlativoActual;

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($notaCredito->ticketConsultaSunat != '') {
            return SunatHelper::getStatus($notaCredito->ticketConsultaSunat, $empresa, $fileName);
        }

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::createVoidedCreditNoteXml($notaCredito, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumary($fileName, $xml,  $empresa, $certificado, $correlativo, $currentDate);
    }
}
