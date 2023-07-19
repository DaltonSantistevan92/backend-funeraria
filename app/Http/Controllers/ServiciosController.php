<?php

namespace App\Http\Controllers;
use App\Models\Categoria;
use App\Models\Servicios;
use Illuminate\Http\Request;

class ServiciosController extends Controller{

    public function listarCategoriaServicios(){
        //$categoria = Categoria::where('estado','A')->where('id','<>',1)->where('id','<>',2)->get();
        $categoria = Categoria::where('estado','A')->where('pertenece','S')->get();
        $response = [];

        if($categoria){
            foreach($categoria as $cat){
                $cat->servicios;
            }

            $response = [
                'status' => true,
                'message' => 'Se encontró categorias',
                'data' => $categoria
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'No se encontró categorias',
                'data' => null
            ];
        }
        return response()->json($response);
    }


    public function listarServiciosSoloPlan(){
        $planes = 3;
        $servicio = Servicios::where('estado','A')->where('categoria_id',$planes)->get();
        $response = [];

        if($servicio){  
            $response = [
                'status'=> true,
                'message'=>'existen datos',
                'data' => $servicio
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