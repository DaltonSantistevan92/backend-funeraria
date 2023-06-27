<?php

namespace App\Http\Controllers;

use App\Models\Repartidor;
use Illuminate\Http\Request;

class RepartidorController extends Controller
{
    public function listarRepartidor(){
        $repartidor = Repartidor::where('estado','A')->get();
        $response = [];
        
        if($repartidor){
            foreach($repartidor as $re){
                $re->persona;
            }

            $response = [
                'status'=> true,
                'message'=>'Existen datos',
                'data' => $repartidor
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
