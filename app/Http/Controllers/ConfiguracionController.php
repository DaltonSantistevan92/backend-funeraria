<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function getConfi(){
        $configuraciones = Configuracion::all();
        return response()->json($configuraciones);
    }
}
