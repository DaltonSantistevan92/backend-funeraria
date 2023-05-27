<?php

use App\Http\Controllers\AfiliadoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Duracion_MesController;
use App\Http\Controllers\Estado_CivilController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\ParentescoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ServiciosController;
use Illuminate\Http\Request;
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

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */


Route::post('loginMovil', [AuthController::class, 'loginAppMovil']);
Route::post('loginWeb', [AuthController::class, 'loginAppWeb']);

Route::post('crearCuenta', [AuthController::class, 'crearCuenta']);

Route::middleware('jwt.verify')->group( function () {

    Route::get('roles', [AuthController::class, 'getRoles']);

    Route::get('categoriasProductos', [ProductoController::class, 'listarCategoriaProducto']);
    Route::get('categoriasServicios', [ServiciosController::class, 'listarCategoriaServicios']);
    Route::get('servicioSoloPlan', [ServiciosController::class, 'listarServiciosSoloPlan']);

    Route::get('estado_civil', [Estado_CivilController::class, 'listarEstadoCivil']);

    Route::get('parentesco', [ParentescoController::class, 'listarParentesco']);

    Route::get('duracion_mes', [Duracion_MesController::class, 'listarDuracionMes']);
    
    Route::get('verificacionAfiliacion/{cliente_id}', [AfiliadoController::class, 'verificarAfiliacion']);
    Route::post('guardarAfiliado',[AfiliadoController::class,'guardarAfiliado']);
    Route::get('tableAfiliado/{estado_id}', [AfiliadoController::class, 'tableAfiliado']);

    Route::get('estados', [EstadoController::class, 'listarEstados']);






});

