<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use Illuminate\Http\Request;

class ProvinciaController extends Controller
{
    public function mostrarProvinciaCantonParroquia(){
        $provincias = Provincia::where('estado','A')->get();
        $response = [];

        if ( $provincias->count() > 0) {
           
            $response = [
                'status' => true,
                'message' => 'exiten datos',
                'data' => $provincias
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'no exiten datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }
}
