<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function listarEstados(){
        $estado = Estado::where('estado','A')->get();
        $response = [];
        if($estado){

            $response = [
                'status'=> true,
                'message'=>'existen datos',
                'data' => $estado
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
