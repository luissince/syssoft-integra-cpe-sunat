<?php

use App\Http\Controllers\EmpresaCotroller;
use App\Http\Controllers\GuiaRemisionController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("/create", [EmpresaCotroller::class, 'create']);

Route::get("/consultar/{ruc}/{usuarioSol}/{claveSol}/{tipoComprobante}/{serie}/{numeracion}", [EmpresaCotroller::class, 'consultar']);

Route::get("/boleta/{idVenta}", [VentaController::class, 'index']);

Route::get("/resumen/{idVenta}", [VentaController::class, 'resumenDiario']);

Route::get("/comunicaciondebaja/{idVenta}", [VentaController::class, 'comunicacionDeBaja']);

Route::get("/guiaremision/{idGuiaRemision}", [GuiaRemisionController::class, 'index']);

/**
 * Api V1
 */

Route::post("/v1/facturar", [VentaController::class, 'sendBoletaOrFactura']);

Route::post("/v1/anular/boleta", [VentaController::class, 'sendResumenDiario']);

Route::post("/v1/anular/factura", [VentaController::class, 'sendComunicacionDeBaja']);

Route::post("/v1/guia/remision", [GuiaRemisionController::class, 'sendGuiaRemision']);

Route::post("/v1/consultar", [EmpresaCotroller::class, 'sendConsulta']);
