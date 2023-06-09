<?php

use App\Http\Controllers\AfiliadoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\Duracion_MesController;
use App\Http\Controllers\Estado_CivilController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\ParentescoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ServiciosController;
use App\Http\Controllers\ToolController;
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

//RUTAS PUBLICAS
Route::post('loginMovil', [AuthController::class, 'loginAppMovil']);
Route::post('loginWeb', [AuthController::class, 'loginAppWeb']);
Route::post('crearCuenta', [AuthController::class, 'crearCuenta']);

//RUTAS PROTEGIGAS POR JWT
Route::middleware('jwt.verify')->group( function () {
    Route::get('roles', [AuthController::class, 'getRoles']);

    Route::get('categoriasProductos', [ProductoController::class, 'listarCategoriaProducto']);
    Route::get('getProductos', [ProductoController::class, 'listarProducto']);
    Route::post('saveProducto', [ProductoController::class, 'guardarProducto']);
    Route::post('updateProducto',[ProductoController::class,'actualizarProducto']);
    Route::get('deleteProducto/{producto_id}', [ProductoController::class, 'eliminarProducto']);

    Route::get('estado_civil', [Estado_CivilController::class, 'listarEstadoCivil']);

    Route::get('parentesco', [ParentescoController::class, 'listarParentesco']);

    Route::get('duracion_mes', [Duracion_MesController::class, 'listarDuracionMes']);
    
    Route::get('verificacionAfiliacion/{cliente_id}', [AfiliadoController::class, 'verificarAfiliacion']);
    Route::post('guardarAfiliado',[AfiliadoController::class,'guardarAfiliado']);
    Route::get('tableAfiliado/{estado_id}', [AfiliadoController::class, 'tableAfiliado']);
    Route::get('setEstadoAfiliado/{afiliado_id}/{estado_id}',[AfiliadoController::class, 'cambioEstado']);

    Route::get('estados', [EstadoController::class, 'listarEstados']);

    Route::get('categorias', [CategoriaController::class, 'listarCategorias']);
    Route::post('saveCategoria',[CategoriaController::class,'guardarCategoria']);
    Route::post('updateCategoria',[CategoriaController::class,'actualizarCategoria']);
    Route::get('deleteCategoria/{categoria_id}', [CategoriaController::class, 'deleteCategorias']);
    Route::get('listarProductoPorCategoria/{categoria_id}', [CategoriaController::class, 'listarProductoPorCategoria']);

    Route::get('categoriasServicios', [ServiciosController::class, 'listarCategoriaServicios']);
    Route::get('servicioSoloPlan', [ServiciosController::class, 'listarServiciosSoloPlan']);
    
    Route::get('servicios', [ServicioController::class, 'listarServicios']);
    Route::post('saveServicio',[ServicioController::class,'guardarServicio']);
    Route::post('updateServicio',[ServicioController::class,'actualizarServicio']);
    Route::get('deleteServicio/{servicio_id}', [ServicioController::class, 'deleteServicio']);

    Route::get('proveedores', [ProveedorController::class, 'listarProveedores']);
    Route::post('saveProveedor',[ProveedorController::class,'guardarProveedor']);
    Route::post('updateProveedor',[ProveedorController::class,'actualizarProveedor']);
    Route::get('deleteProveedor/{proveedor_id}', [ProveedorController::class, 'deleteProveedor']);

    Route::post('saveCatalogo',[CatalogoController::class,'guardarCatalogo']);
    Route::get('productosPorProveedor/{proveedor_id}',[CatalogoController::class,'productosPorProveedor']);
    Route::get('mostrarProveedoresDeCatalago',[CatalogoController::class,'mostrarProveedoresDeCatalago']);

    Route::get('tableCompras/{estado_id}', [CompraController::class, 'tableCompras']);


});


//RUTAS POR CORS DE ARCHIVOS
Route::group(['middleware' => ['cors']], function () { 
    Route::get('mostrarImagen/{carpeta}/{archivo}',[ ToolController::class, 'mostrarImagen']);
    Route::post('subirArchivo',[ ToolController::class, 'subirArchivo' ]);
});
