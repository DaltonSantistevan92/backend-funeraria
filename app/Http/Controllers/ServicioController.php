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

    public function guardarServicio(Request $request){
        $servicioRequest = (object)$request->servicio;
        $nombre = ucfirst($servicioRequest->nombre);

        if ($servicioRequest) {
            $existeServicio = Servicios::where('nombre', $nombre)->get()->first();

            if ($existeServicio) {
                $response = [
                    'status' => false,
                    'message' => 'El servicio ' .$nombre .' ya existe',
                    'data' => null,
                ];
            } else {
                $nuevoServicio = new Servicios();
                $nuevoServicio->categoria_id = intval($servicioRequest->categoria_id);
                $nuevoServicio->nombre = $nombre;
                $nuevoServicio->descripcion = ucfirst($servicioRequest->descripcion);
                $nuevoServicio->precio = floatval($servicioRequest->precio);
                $nuevoServicio->imagen = $servicioRequest->imagen;
                $nuevoServicio->estado = 'A';

                if ($nuevoServicio->save()) {
                    $response = [
                        'status' => true,
                        'message' => 'El servicio se registro con exito',
                        'data' => $nuevoServicio,
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'El servicio no se puede registrar',
                        'data' => null,
                    ];
                }
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'No hay datos para procesar',
                'data' => null,
            ];
        }
       return response()->json($response);
    }

    public function actualizarServicio(Request $request){
        $servicioRequest = (object)$request->servicio;

        $id = intval($servicioRequest->id);
        $nombre = ucfirst($servicioRequest->nombre);
        $response=[];

        $servicio = Servicios::find($id);

        if($servicioRequest){
            if($servicio){
                $servicio->categoria_id = intval($servicioRequest->categoria_id);
                $servicio->nombre = $nombre;
                $servicio->descripcion = ucfirst($servicioRequest->descripcion);
                $servicio->precio = floatval($servicioRequest->precio);
                $servicio->imagen = $servicioRequest->imagen;
                $servicio->estado = 'A';
                $servicio->save();

                $response = [
                    'status' => true,
                    'message' => 'El servicio se actualizo con exito',
                    'data' => $servicio
                ];
            }else{
                $response=[
                    'status'=> true,
                    'message'=>'No se puede actualizar el servicio',
                    'data'=> null
                ];
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'No existen datos',
            ];
        }
        return response()->json($response);
    }

    public function deleteServicio($servicio_id){
        $servicio = Servicios::find(intval($servicio_id));
        $response = [];
        
        if($servicio){
            $servicio->estado = 'I';
            $servicio->save();

            $response = [
                'status'=> true,
                'message'=>'El servicio ' . $servicio->nombre . ' ah sido eliminado',
                'data' => $servicio
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No se puede eliminar el servicio ' . $servicio->nombre,
                'data' => null
            ];
        }
        return response()->json($response);

    }
}
