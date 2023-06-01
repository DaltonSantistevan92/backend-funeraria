<?php

namespace App\Http\Controllers;

use App\Models\Servicios;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function listarServicios(){
        $servicios = Servicios::where('estado','A')->get();
        $response = [];
        
        if($servicios){
            foreach($servicios as $ser){
                $ser->categoria;
            }
            
            $response = [
                'status'=> true,
                'message'=>'Existen datos',
                'data' => $servicios
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
