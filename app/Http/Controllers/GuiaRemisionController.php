<?php

namespace App\Http\Controllers;

use App\Helpers\SunatHelper;
use App\Helpers\XmlGenerator;
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
            return SunatHelper::getStatusDespatchAdvice($fileName, $idGuiaRemision, $guiaRemision, $empresa, $guiaRemision->numeroTicketSunat);
        }

        $detalle = $this->guiaRemisionRepository->obtenerDetalleGuiaRemisionPorId($idGuiaRemision);

        $xml = XmlGenerator::generateDespatchAdviceXml($guiaRemision, $detalle, $empresa);

        return SunatHelper::sendDespatchAdvice($fileName, $xml, $idGuiaRemision, $guiaRemision, $empresa);
    }
}
