<?php

namespace App\Http\Controllers;

use App\Models\Parentesco;
use Illuminate\Http\Request;

class ParentescoController extends Controller
{
    public function listarParentesco(){
        $parentesco = Parentesco::where('estado','A')->get();
        $response = [];
        if($parentesco){

            $response = [
                'status'=> true,
                'message'=>'existen datos',
                'data' => $parentesco
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
