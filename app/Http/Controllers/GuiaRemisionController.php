<?php

namespace App\Http\Controllers;

use App\Helpers\SunatHelper;
use App\Helpers\XmlGenerator;
use App\Models\Certificado;
use App\Models\Empresa;
use App\Models\GuiaRemision\Detalle;
use App\Models\GuiaRemision\GuiaRemision;
use App\Repositories\EmpresaRepository;
use App\Repositories\GuiaRemisionRepository;
use App\Repositories\SucursalRepository;
use Illuminate\Http\Request;

class GuiaRemisionController extends Controller
{
    private $guiaRemisionRepository;

    private $empresaRepository;

    private $sucursalRepository;

    public function __construct(
        GuiaRemisionRepository $guiaRemisionRepository,
        EmpresaRepository $empresaRepository,
        SucursalRepository $sucursalRepository
    ) {
        $this->guiaRemisionRepository = $guiaRemisionRepository;
        $this->empresaRepository = $empresaRepository;
        $this->sucursalRepository = $sucursalRepository;
    }

    public function index($idGuiaRemision)
    {
        $guiaRemision = $this->guiaRemisionRepository->obtenerGuiaRemisionPorId($idGuiaRemision);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        $fileName = $empresa->documento . "-" . $guiaRemision->codigo . "-" . $guiaRemision->serie . "-" . $guiaRemision->numeracion;

        if ($guiaRemision->numeroTicketSunat !== "") {
            return SunatHelper::getStatusDespatchAdviceToSunat($fileName, $idGuiaRemision, $guiaRemision, $empresa, $guiaRemision->numeroTicketSunat);
        }

        $detalle = $this->guiaRemisionRepository->obtenerDetalleGuiaRemisionPorId($idGuiaRemision);

        $xml = XmlGenerator::generateDespatchAdviceXml($guiaRemision, $detalle, $empresa);

        return SunatHelper::sendDespatchAdviceSunat($fileName, $xml, $idGuiaRemision, $guiaRemision, $empresa);
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
