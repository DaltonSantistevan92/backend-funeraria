<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function guardarCatalogo(Request $request){
        $catalogoRequest = (object) $request->catalogo;
        $response = [];
       
        if ($catalogoRequest) {
            $precio = doubleval($catalogoRequest->precio);

            $nuevoCatalogo = new Catalogo();
            $nuevoCatalogo->proveedor_id = $catalogoRequest->proveedor_id;
            $nuevoCatalogo->producto_id = $catalogoRequest->producto_id;
            $nuevoCatalogo->estado = 'A';

            //existe el proveedor con su producto
            //$catalogoExiste = Catalogo::where('proveedor_id', $catalogoRequest->proveedor_id)->where('producto_id', $catalogoRequest->producto_id)->get()->first();

            $catalogoExisteProducto = Catalogo::where('producto_id', $catalogoRequest->producto_id)->get()->first();

            if ($catalogoExisteProducto) {
                $catalogoExisteProducto->proveedor_id = $catalogoRequest->proveedor_id;
                $catalogoExisteProducto->save();

                $nombreProducto = $catalogoExisteProducto->producto->nombre;
                $this->updatePrecioCompraProducto($catalogoRequest->producto_id, $precio);

                $response = [ 'status' => true, 'message' => 'El Precio del producto ' .$nombreProducto . ' Actualizado' ];
            }else {
                if($nuevoCatalogo->save()){
                    $this->updatePrecioCompraProducto($catalogoRequest->producto_id, $precio);

                    $response = [ 'status' => true, 'message' => 'El producto ' . $nuevoCatalogo->producto->nombre . ' se asigno su precio de compra','data' => $nuevoCatalogo ];
                }else{
                    $response = [ 'status' => false, 'message' => 'No se pudo guardar la información', 'data' => null ];
                }
            }
        }else {
            $response = [ 'status' => false, 'message' => 'No ha enviado datos', 'data' => null ];
        }
        return response()->json($response);
    }

    protected function updatePrecioCompraProducto($producto_id, $precio)
    {
        $producto = Producto::find($producto_id);
        $producto->precio_compra = $precio;
        $producto->save();
    }

    /**
    * @Descripcion cuando seleeciona el proveedor_id muestra los productos que fueron asignado el precio de compra
    */
    public function productosPorProveedor($proveedor_id){
        $catalogo = Catalogo::where('proveedor_id',intval($proveedor_id))->get();
        $response = [];  $productos = [];

        if ($catalogo->count() > 0) {
            foreach($catalogo as $ca){
                $proveedor = $ca->proveedor;
                $productos[] = collect($ca->producto, $ca->producto->categoria);
            }
            
            $response = [ 
                'status' => true, 
                'message' => 'existen datos', 
                'data' => [
                    'proveedor' => $proveedor,
                    'producto' => $productos
                ] ];
        } else {
            $response = [ 
                'status' => false, 
                'message' => 'no existen datos', 
                'data' => null 
            ];
        }
        return response()->json($response);
    }

    /**
    * @Descripcion select de compra , solo mostrara los provedores que esten en catalogo  y se repiten valñida que solo sea unico.
    */
    public function mostrarProveedoresDeCatalago(){//
        $catalogoProveedor = Catalogo::all();

        if ($catalogoProveedor->count() > 0) {
            $proveedor = collect();
        
            foreach ($catalogoProveedor as $ca) {
                $proveedor->push($ca->proveedor);
            }
        
            $proveedor = $proveedor->unique();
        
            $response = [
                'status' => true,
                'message' => 'existen datos',
                'data' => $proveedor->values()->all()
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'no existen datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }
}
