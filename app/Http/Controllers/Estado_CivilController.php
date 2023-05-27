<?php

namespace App\Http\Controllers;

use App\Models\Estado_Civil;
use Illuminate\Http\Request;

class Estado_CivilController extends Controller
{
    public function listarEstadoCivil(){
        $estadoCivil = Estado_Civil::where('estado','A')->get();
        $response = [];
        if($estadoCivil){

            $response = [
                'status'=> true,
                'message'=>'existen datos',
                'data' => $estadoCivil
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
