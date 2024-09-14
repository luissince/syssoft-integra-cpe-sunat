<?php

namespace App\Http\Controllers;

use App\Helpers\SunatHelper;
use App\Helpers\XmlGenerator;
use App\Models\Certificado;
use App\Models\Empresa;
use App\Models\GuiaRemision\Detalle;
use App\Models\GuiaRemision\GuiaRemision;
use Illuminate\Http\Request;

class GuiaRemisionController extends Controller
{
    public function __construct() {
        
    }

    public function sendGuiaRemision(Request $request)
    {
        $guiaRemision = new GuiaRemision($request->guiaRemision);
        $empresa = new Empresa($request->empresa);
        $certificado = new Certificado($request->certificado);

        $fileName = $empresa->documento . "-" . $guiaRemision->codigo . "-" . $guiaRemision->serie . "-" . $guiaRemision->numeracion;

        if ($guiaRemision->numeroTicketSunat !== "") {
            return SunatHelper::getStatusDespatchAdvice($fileName, $guiaRemision, $empresa, $guiaRemision->numeroTicketSunat);
        }

        $detalles = [];
        foreach ($request->detalle as $detalleData) {
            $detalle = new Detalle($detalleData);
            $detalles[] = $detalle;
        }
        
        $xml = XmlGenerator::createDespatchAdviceXml($guiaRemision, $detalles, $empresa);

        return SunatHelper::sendDespatchAdvice($fileName, $xml, $guiaRemision, $empresa, $certificado);
    }
}
