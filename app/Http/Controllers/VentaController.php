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

    public function index(string $idVenta)
    {
        $venta = $this->ventaRepository->obtenerVentaPorId($idVenta);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        $sucursal = $this->sucursalRepository->obtenerSucursalPorId($venta->idSucursal);

        $detalle = $this->ventaRepository->obtenerDetalleVentaPorId($idVenta);

        $xml = XmlGenerator::generateInvoiceXml($venta, $detalle, $empresa, $sucursal);

        $fileName = $empresa->documento . '-' . $venta->codigoVenta . '-' . $venta->serie . '-' . $venta->numeracion;

        return SunatHelper::sendBillToSunat($fileName, $xml, $idVenta, $empresa);
    }

    public function resumenDiario(string $idVenta)
    {

        $venta = $this->ventaRepository->obtenerVentaPorId($idVenta);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        $correlativoActual = $this->ventaRepository->obteneCorrelativoResumenDiario();

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($venta->ticketConsultaSunat != '') {
            return SunatHelper::getStatusToSunat($idVenta, $venta, $empresa, $fileName);
        }

        $detalle = $this->ventaRepository->obtenerDetalleVentaPorId($idVenta);

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::generateSummaryDocumentsXml($venta, $detalle, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumaryToSunat($fileName, $xml, $idVenta, $empresa, $correlativo, $currentDate);
    }

    public function comunicacionDeBaja(string $idVenta)
    {
        $venta = $this->ventaRepository->obtenerVentaPorId($idVenta);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        $correlativoActual = $this->ventaRepository->obteneCorrelativoResumenDiario();

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($venta->ticketConsultaSunat != '') {
            return SunatHelper::getStatusToSunat($idVenta, $venta, $empresa, $fileName);
        }

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::generateVoidedDocumentsXml($venta, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumaryToSunat($fileName, $xml, $idVenta, $empresa, $correlativo, $currentDate);
    }
}
