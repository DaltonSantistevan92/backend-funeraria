<?php

namespace App\Http\Controllers;

use App\Models\Duracion_Mes;
use Illuminate\Http\Request;

class Duracion_MesController extends Controller
{
    public function listarDuracionMes(){
        $duracionMes = Duracion_Mes::where('estado','A')->get();
        $response = [];
        if($duracionMes){

            $response = [
                'status'=> true,
                'message'=>'existen datos',
                'data' => $duracionMes
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
