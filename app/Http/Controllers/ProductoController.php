<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{

    public function listarCategoriaProducto(){
        $categoria = Categoria::where('estado','A')->where('id','<>',3)->get();
        $response = [];

        if($categoria){
            foreach($categoria as $item){
                $item->producto;
            }

            $response = [
                'status'=> true,
                'message'=>'Se encontró categorias',
                'data' => $categoria
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No existe información',
                'categoria'=>null,
            ];
        }
        return response()->json($response);
    }

}
