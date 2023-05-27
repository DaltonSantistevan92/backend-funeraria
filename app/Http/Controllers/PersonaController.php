<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator,Hash};


class PersonaController extends Controller
{

    public function guardarPersona( $data ){
        $response = [];

        if ($data) {
            $newPersona = new Persona();
            $newPersona->nombres = $data['name'];
            $newPersona->estado = 'A';

            if ($newPersona->save()) {
                $response = [ 'status'=> true, 'message' => 'Se registro con exito', 'persona' => $newPersona ];
            }else{
                $response = [ 'status'=> false, 'message' => 'No se pudo registrar'];
            }
        } else {
            $response = [ 'status'=> false, 'message' => 'No existe data'];
        }
        return $response;
    }
}
