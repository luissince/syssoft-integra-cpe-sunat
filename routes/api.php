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

Route::get("/boleta/{idVenta}",[VentaController::class, 'index']);

Route::get("/resumen/{idVenta}", [VentaController::class, 'resumenDiario']);

Route::get("/guiaremision/{idGuiaRemision}", [GuiaRemisionController::class, 'index']);