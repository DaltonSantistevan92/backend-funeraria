<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function listarCategorias(){
        $categorias = Categoria::where('estado','A')->get();
        $response = [];
        
        if($categorias){

            $response = [
                'status'=> true,
                'message'=>'Existen datos',
                'data' => $categorias
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No existen datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }


    public function selectCategoriasServicios(){
        $categorias = Categoria::where('estado','A')->where('pertenece','S')->get();
        $response = [];
        
        if($categorias){

            $response = [
                'status'=> true,
                'message'=>'Existen datos',
                'data' => $categorias
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No existen datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }

    public function selectCategoriasProductos(){
        $categorias = Categoria::where('estado','A')->where('pertenece','P')->get();
        $response = [];
        
        if($categorias){

            $response = [
                'status'=> true,
                'message'=>'Existen datos',
                'data' => $categorias
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No existen datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }

    public function guardarCategoria(Request $request){
        $categoriaRequest = (object)$request->categoria;
        $nombre_categoria = ucfirst($categoriaRequest->nombre_categoria);

        if ($categoriaRequest) {
            $existeCategoria = Categoria::where('nombre_categoria', $nombre_categoria)->get()->first();

            if ($existeCategoria) {
                $response = [
                    'status' => false,
                    'message' => 'La categoria ya existe',
                    'data' => null,
                ];
            } else {
                $nuevaCategoria = new Categoria();
                $nuevaCategoria->nombre_categoria = $nombre_categoria;
                $nuevaCategoria->img = $categoriaRequest->img;
                $nuevaCategoria->pertenece = $categoriaRequest->pertenece;
                $nuevaCategoria->estado = 'A';

                if ($nuevaCategoria->save()) {
                    $response = [
                        'status' => true,
                        'message' => 'La categoria se ha guardado',
                        'data' => $nuevaCategoria,
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'La categoria no se puede guardar',
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

    public function actualizarCategoria(Request $request){
        $categoriaRequest = (object)$request->categoria;

        $id = intval($categoriaRequest->id);
        $nombre_categoria = ucfirst($categoriaRequest->nombre_categoria);
        $response=[];

        $categoria = Categoria::find($id);

        if($categoriaRequest){
            if($categoria){
                $categoria->nombre_categoria = $nombre_categoria;
                $categoria->img = $categoriaRequest->img;
                $categoria->pertenece = $categoriaRequest->pertenece;
                $categoria->save();

                $response=[
                    'status' => true,
                    'message' => 'La Categoria ah sido actualizada',
                    'data' => $categoria
                ];
            }else{
                $response=[
                    'status'=> true,
                    'message'=>'No se puede actualizar la categoria',
                    'data'=> null
                ];
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'No existen datos',
            ];
        }
        return response()->json($response);
    }

    public function deleteCategorias($categoria_id){
        $categoria = Categoria::find(intval($categoria_id));
        $response = [];
        
        if($categoria){
            $categoria->estado = 'I';
            $categoria->save();

            $response = [
                'status'=> true,
                'message'=>'La Categoria ' . $categoria->nombre_categoria . ' ah sido eliminada',
                'data' => $categoria
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No se puede eliminar la categoria ' . $categoria->nombre_categoria,
                'data' => null
            ];
        }
        return response()->json($response);
    }

    public function listarProductoPorCategoria($categoria_id){
        $productos = Producto::where('categoria_id',$categoria_id)->get();
        $response = [];
        
        if($productos){
            $response = [
                'status'=> true,
                'message'=>'Existen datos',
                'data' => $productos
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No existen datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }
}
