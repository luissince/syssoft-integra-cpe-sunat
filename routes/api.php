<?php

use App\Http\Controllers\EmpresaController;
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

/**
 * Api V1
 */

// Ruta para manejar la solicitud de facturación
Route::post("/v1/facturar", [VentaController::class, 'sendBoletaOrFactura']);

// Ruta para manejar la anulación de una boleta
Route::post("/v1/anular/boleta", [VentaController::class, 'sendResumenDiario']);

// Ruta para manejar la anulación de una factura
Route::post("/v1/anular/factura", [VentaController::class, 'sendComunicacionDeBaja']);

// Ruta para manejar el envío de una guía de remisión
Route::post("/v1/guia/remision", [GuiaRemisionController::class, 'sendGuiaRemision']);

// Ruta para manejar el estado de un comprobante
Route::post("/v1/consultar", [EmpresaController::class, 'getStatus']);

// Ruta para manejar el cdr de un comprobante
Route::post("/v1/cdr", [EmpresaController::class, 'getCdr']);


