<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function tableCompras($estado_id){
        $compra = Compra::where('estado_id',intval($estado_id))->get();
        $response = [];

        if ($compra->count() > 0) {
            foreach($compra as $c){
                $c->user->persona;
                $c->proveedor;
                $c->estado;

                foreach($c->detalle_compra as $dc){
                    $dc->producto;
                }
            }
            $response = [
                'status'=> true,
                'message' => 'existen datos',
                'data' => $compra
            ];  
        }else {
            $response = [
                'status'=> false,
                'message' => 'no existen datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }
}
