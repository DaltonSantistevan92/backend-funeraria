<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;



class ToolController extends Controller
{
    public function mostrarImagen($carpeta,$archivo){
        $existeArchivo = Storage::disk($carpeta)->exists($archivo);
        $response = [];
        
        if($existeArchivo){
            $file = Storage::disk($carpeta)->get($archivo);
            return new Response($file,200);
        }else{
            $response=[
                'status' => false,
                'message' => 'No existe la imagen',
                'imagen' => null
            ];
        }
        return response()->json($response);
        
     }
 
     public function subirArchivo(Request $request){
         if($request->hasFile('img-0')){
             $imagen = $request->file('img-0');
             $filenamewithextension = $imagen->getClientOriginalName();   //Archivo con su extension   
             $folder = $request->input('folder');
             Storage::disk($folder)->put($filenamewithextension, fopen($request->file('img-0'), 'r+'));
             
             $response = [
                 'status' => true,
                 'message' => 'La imagen se ha subido al servidor',
                 'imagen' => $filenamewithextension,
             ];
         }else{
             $response = [
                 'status' => false,
                 'message' => 'No hay un archivo para procesar',
                 'imagen' => '',
             ];
         }
         return response()->json($response);
     }
}
