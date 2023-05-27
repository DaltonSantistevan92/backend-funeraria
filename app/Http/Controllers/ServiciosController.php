<?php

namespace App\Http\Controllers;
use App\Models\Categoria;
use App\Models\Servicios;
use Illuminate\Http\Request;

class ServiciosController extends Controller{

    public function listarCategoriaServicios(){
        $categoria = Categoria::where('estado','A')->where('id','<>',1)->where('id','<>',2)->get();
        $response = [];
        if($categoria){
            foreach($categoria as $cat){
                $cat->servicios;
            }

            $response = [
                'status'=> true,
                'message'=>'Se encontró categorias',
                'data' => $categoria
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No se encontró categorias',
                'data' => null
            ];
        }

        return response()->json($response);
    }

    public function listarServiciosSoloPlan(){
        $servicio = Servicios::where('estado','A')->where('categoria_id',3)->get();
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