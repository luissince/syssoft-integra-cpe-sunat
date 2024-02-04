<?php

namespace App\Http\Controllers;

use App\Helpers\SunatHelper;
use App\Helpers\XmlGenerator;
use App\Repositories\EmpresaRepository;
use App\Repositories\SucursalRepository;
use App\Repositories\VentaRepository;
use DateTime;

class VentaController extends Controller
{
    private $ventaRepository;

    private $empresaRepository;

    private $sucursalRepository;

    public function __construct(
        VentaRepository $ventaRepository,
        EmpresaRepository $empresaRepository,
        SucursalRepository $sucursalRepository
    ) {
        $this->ventaRepository = $ventaRepository;
        $this->empresaRepository = $empresaRepository;
        $this->sucursalRepository = $sucursalRepository;
    }

    public function index($idVenta)
    {
        $venta = $this->ventaRepository->obtenerVentaPorId($idVenta);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        $sucursal = $this->sucursalRepository->obtenerSucursalPorId($venta->idSucursal);

        $detalle = $this->ventaRepository->obtenerDetalleVentaPorId($idVenta);

        $xml = XmlGenerator::generateInvoiceXml($venta, $detalle, $empresa, $sucursal);

        $fileName = $empresa->documento . '-' . $venta->codigoVenta . '-' . $venta->serie . '-' . $venta->numeracion;

        return SunatHelper::sendBillToSunat($fileName, $xml, $idVenta, $empresa);
    }

    public function resumenDiario($idVenta)
    {

        $venta = $this->ventaRepository->obtenerVentaPorId($idVenta);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        if($venta->ticketConsultaSunat != ''){
            return SunatHelper::getStatusToSunat( $idVenta, $venta, $empresa);
        }

        $detalle = $this->ventaRepository->obtenerDetalleVentaPorId($idVenta);

        $correlativoActual = $this->ventaRepository->obteneCorrelativoResumenDiario();

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $currentDate = new DateTime('now');

        $xml = XmlGenerator::generateDailySummaryXml($venta, $detalle, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumaryToSunat($fileName, $xml, $idVenta, $empresa, $correlativo, $currentDate);
    }
}
 