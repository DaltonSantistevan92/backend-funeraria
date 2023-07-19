<?php

namespace App\Http\Controllers;

use App\Models\{Categoria,Producto};
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    private $limiteCodigo = 10;


    public function listarCategoriaProducto(){
        //$categoria = Categoria::where('estado','A')->where('id','<>',3)->get();
        $categoria = Categoria::where('estado','A')->where('pertenece','P')->get();

        $response = [];

        if($categoria){
            foreach($categoria as $item){
                $item->producto;
            }

            $response = [
                'status' => true,
                'message' => 'Se encontró categorias',
                'data' => $categoria
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'No existe información',
                'data' => null,
            ];
        }
        return response()->json($response);
    }

    public function listarProducto(){
        $producto = Producto::where('estado','A')->get();
        $response = [];

        if($producto){
            foreach($producto as $item){
                $item->categoria;
            }

            $response = [
                'status' => true,
                'message' =>'Existen datos',
                'data' => $producto
            ];
        }else{
            $response = [
                'status' => false,
                'message' =>'No existe datos',
                'data' => null,
            ];
        }
        return response()->json($response);
    }

    public function generate_key($limit){
        $key = '';

        $aux = sha1(md5(time()));
        $key = substr($aux, 0, $limit);

        return $key;
    }

    public function guardarProducto(Request $request){
        $productoRequest = (object) $request->producto;
        $nombre = ucfirst($productoRequest->nombre);
        $codigoAutomatico = $this->generate_key($this->limiteCodigo);
        $response = [];

        if ($productoRequest) { 
            $existe = Producto::where('codigo',$codigoAutomatico)->orWhere('nombre',$nombre)->get()->first();
            
            if ($existe) {
                $response = [
                    'status' => false,
                    'message' => 'El ' . ($existe->codigo === $codigoAutomatico ? 'código' : 'nombre') . ' del producto ya existe',
                    'data' => null,
                ];
            } else {
                $nuevoProducto = new Producto();
                $nuevoProducto->categoria_id = intval($productoRequest->categoria_id);
                $nuevoProducto->codigo = $codigoAutomatico;
                $nuevoProducto->nombre = $nombre;
                $nuevoProducto->descripcion = ucfirst($productoRequest->descripcion);
                $nuevoProducto->imagen = $productoRequest->imagen;
                $nuevoProducto->stock = 0;
                $nuevoProducto->precio_compra = 0.00;
                $nuevoProducto->precio_venta = 0.00;
                $nuevoProducto->margen_ganancia = 0.00;
                $nuevoProducto->fecha = date('Y-m-d');
                $nuevoProducto->promocion = 'N';
                $nuevoProducto->precio_anterior = 0.00;
                $nuevoProducto->estado = 'A';
                
                if ($nuevoProducto->save()) {
                    $response = [
                        'status' => true,
                        'message' => 'Producto se registro con exito',
                        'data' => $nuevoProducto,
                    ];
                } else {
                    $response = [
                        'status' => true,
                        'message' => 'No se puede registrar el producto, intente nuevamente',
                        'data' => null,
                    ];
                }
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'No hay datos para procesar',
                'data' => null,
            ];
        }
        return response()->json($response);
    }

    public function actualizarProducto(Request $request){
        $requestProducto = (object) $request->producto;
        $id = intval($requestProducto->id);

        $productos = Producto::find($id);
        $response = [];

        if($requestProducto){
            if($productos){
                $productos->categoria_id = intval($requestProducto->categoria_id);
                $productos->nombre = ucfirst($requestProducto->nombre);
                $productos->descripcion = $requestProducto->descripcion;
                $productos->imagen = $requestProducto->imagen;
                $productos->save();

                $response = [
                    'status' => true,
                    'message' => 'El Producto se actualizo con exito',
                    'data' => $productos
                ];
            }else{
                $response = [
                    'status' => false,
                    'message' => 'No se pudo actualizar el producto',
                    'data' => null
                ];
            }
        }else{
            $response = [
                'status' => false,
                'message' => 'No hay datos'
            ];
        }
        return response()->json($response);
    }

    public function eliminarProducto($producto_id){
        $response = [];
        $producto = Producto::find(intval($producto_id));

        if($producto){
            $producto->estado = 'I';
            $producto->save();
            
            $response = [
                'status' => true,
                'message' => 'Se ha eliminado el producto',
                'data' => $producto             
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'No se puede eliminar el producto',
                'data' => null             
            ];
        }
        return response()->json($response);
    }

}
