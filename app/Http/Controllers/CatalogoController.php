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

            $catalogoExiste = Catalogo::where('proveedor_id', $catalogoRequest->proveedor_id)->where('producto_id', $catalogoRequest->producto_id)->get()->first();
            
            if ($catalogoExiste) {
                $nombreProducto = $catalogoExiste->producto->nombre;

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
}
