<?php

namespace App\Http\Controllers;

use App\Helpers\SunatHelper;
use App\Helpers\XmlGenerator;
use App\Models\Detalle;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Repositories\EmpresaRepository;
use App\Repositories\SucursalRepository;
use App\Repositories\VentaRepository;
use DateTime;
use Illuminate\Http\Request;

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

        $detalles = $this->ventaRepository->obtenerDetalleVentaPorId($idVenta);

        $plazos = $this->ventaRepository->obtenerPlazosPorId($idVenta);

        $xml = XmlGenerator::generateInvoiceXml($venta, $detalles, $empresa, $sucursal, $plazos);

        $fileName = $empresa->documento . '-' . $venta->codigoVenta . '-' . $venta->serie . '-' . $venta->numeracion;

        return SunatHelper::sendBillToSunat($fileName, $xml, $idVenta, $empresa);
    }

    public function sendBoletaOrFactura(Request $request)
    {
        $venta = new Venta($request->venta);
        $empresa = new Empresa($request->empresa);
        $sucursal = new Sucursal($request->sucursal);

        $detalles = [];
        foreach ($request->detalle as $detalleData) {
            $detalle = new Detalle($detalleData);
            $detalles[] = $detalle;
        }

        $xml = XmlGenerator::createInvoiceXml($venta, $detalles, $empresa, $sucursal);

        $fileName = $empresa->documento . '-' . $venta->codigoVenta . '-' . $venta->serie . '-' . $venta->numeracion;

        return SunatHelper::sendBill($fileName, $xml, $venta->idVenta, $empresa);
    }

    public function resumenDiario(string $idVenta)
    {

        $venta = $this->ventaRepository->obtenerVentaPorId($idVenta);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        $correlativoActual = $this->ventaRepository->obtenerCorrelativoResumenDiario();

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($venta->ticketConsultaSunat != '') {
            return SunatHelper::getStatusToSunat($idVenta, $venta, $empresa, $fileName);
        }

        $detalles = $this->ventaRepository->obtenerDetalleVentaPorId($idVenta);

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::generateSummaryDocumentsXml($venta, $detalles, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumaryToSunat($fileName, $xml, $idVenta, $empresa, $correlativo, $currentDate);
    }

    public function sendResumenDiario(Request $request)
    {
        $venta = new Venta($request->venta);
        $empresa = new Empresa($request->empresa);
        $correlativoActual = $request->correlativoActual;

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($venta->ticketConsultaSunat != '') {
            return SunatHelper::getStatus($venta->idVenta, $venta, $empresa, $fileName);
        }

        $detalles = [];
        foreach ($request->detalle as $detalleData) {
            $detalle = new Detalle($detalleData);
            $detalles[] = $detalle;
        }
        
        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::createSummaryDocumentsXml($venta, $detalles, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RC-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumary($fileName, $xml, $venta->idVenta, $empresa, $correlativo, $currentDate);
    }

    public function comunicacionDeBaja(string $idVenta)
    {
        $venta = $this->ventaRepository->obtenerVentaPorId($idVenta);

        $empresa = $this->empresaRepository->obtenerEmpresa();

        $correlativoActual = $this->ventaRepository->obtenerCorrelativoResumenDiario();

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

    public function sendComunicacionDeBaja(Request $request)
    {
        $venta = new Venta($request->venta);
        $empresa = new Empresa($request->empresa);
        $correlativoActual = $request->correlativoActual;

        $currentDate = new DateTime('now');

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativoActual;

        if ($venta->ticketConsultaSunat != '') {
            return SunatHelper::getStatus($venta->idVenta, $venta, $empresa, $fileName);
        }

        $correlativo = ($correlativoActual === 0) ? (intval($correlativoActual) + 1) : ($correlativoActual + 1);

        $xml = XmlGenerator::generateVoidedDocumentsXml($venta, $empresa, $correlativo, $currentDate);

        $fileName = $empresa->documento . '-RA-' . $currentDate->format('Ymd') . '-' . $correlativo;

        return SunatHelper::sendSumary($fileName, $xml, $venta->idVenta, $empresa, $correlativo, $currentDate);
    }
}
