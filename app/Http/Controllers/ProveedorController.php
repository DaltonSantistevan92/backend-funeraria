<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function listarProveedores(){
        $proveedor = Proveedor::where('estado','A')->get();
        $response = [];

        if($proveedor){
            $response = [
                'status' => true,
                'message' =>'Existen datos',
                'data' => $proveedor
            ];
        }else{
            $response = [
                'status' => false,
                'message' =>'No existe datos',
                'data' => null,
            ];
        }
        return response()->json($response);
    }

    public function deleteProveedor($proveedor_id){
        $proveedor = Proveedor::find(intval($proveedor_id));
        $response = [];
        
        if($proveedor){
            $proveedor->estado = 'I';
            $proveedor->save();

            $response = [
                'status'=> true,
                'message'=>'El proveedor ' . $proveedor->razon_social . ' ah sido eliminado',
                'data' => $proveedor
            ];
        }else{
            $response = [
                'status'=> false,
                'message'=>'No se puede eliminar el proveedor ' . $proveedor->razon_social,
                'data' => null
            ];
        }
        return response()->json($response);
    }

    public function guardarProveedor(Request $request){
        $proveedorRequest = (object) $request->proveedor;
        $ruc = $proveedorRequest->ruc;
        $razon_social = mb_strtolower($proveedorRequest->razon_social,'UTF-8');  //convierte carateres a minisculas
        $response = [];

        if ($proveedorRequest) { 
            $existe = Proveedor::where('ruc',$ruc)->orWhere('razon_social',$razon_social)->get()->first();
            
            if ($existe) {
                $response = [
                    'status' => false,
                    'message' => ($existe->ruc === $ruc ? ' El ruc ' : 'El nombre o razon social ') . ' del proveedor ya existe',
                    'data' => null,
                ];
            } else {
                $nuevoProveedor = new Proveedor();
                $nuevoProveedor->ruc = $ruc;
                $nuevoProveedor->razon_social = $razon_social;
                $nuevoProveedor->direccion = ucfirst($proveedorRequest->direccion);
                $nuevoProveedor->correo = $proveedorRequest->correo;
                $nuevoProveedor->celular = $proveedorRequest->celular;
                $nuevoProveedor->telefono = $proveedorRequest->telefono;
                $nuevoProveedor->estado = 'A';
                
                if ($nuevoProveedor->save()) {
                    $response = [
                        'status' => true,
                        'message' => 'El Proveedor se registro con exito',
                        'data' => $nuevoProveedor,
                    ];
                } else {
                    $response = [
                        'status' => true,
                        'message' => 'No se puede registrar el proveedor, intente nuevamente',
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

    public function actualizarProveedor(Request $request){
        $proveedorRequest = (object)$request->proveedor;
        $id = intval($proveedorRequest->id);
        $response = [];

        $proveedor = Proveedor::find($id);

        if($proveedorRequest){
            if($proveedor){
                $proveedor->ruc = $proveedorRequest->ruc;
                $proveedor->razon_social = ucfirst($proveedorRequest->razon_social);
                $proveedor->direccion = ucfirst($proveedorRequest->direccion);
                $proveedor->correo = $proveedorRequest->correo;
                $proveedor->celular = $proveedorRequest->celular;
                $proveedor->telefono = $proveedorRequest->telefono;
                $proveedor->save();

                $response = [
                    'status' => true,
                    'message' => 'El proveedor ah sido actualizado',
                    'data' => $proveedor
                ];
            }else{
                $response = [
                    'status'=> true,
                    'message'=>'No se puede actualizar el proveedor',
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
}
