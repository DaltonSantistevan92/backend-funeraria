<?php

namespace App\Http\Controllers;

use App\Models\Afiliado;
use App\Models\Cliente;
use App\Models\Contacto_Emergencia;
use App\Models\Detalle_Afiliado;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class AfiliadoController extends Controller
{
  
    private $permisoCtrl;

    public function __construct()
    {
        $this->permisoCtrl = new PermisoController();
    }


    public function verificarAfiliacion($cliente_id){
        $existeAfiliado = Afiliado::where('cliente_id',$cliente_id)->get()->first();
        $response = [];
       
        if (!$existeAfiliado) {
            $response = [ 'afiliado'=> false,'message' => 'No se encuentra afiliado','icono' => 'alert-outline','color' =>'danger'];
        }else {
            $pendiente = 1;    $activo = 4;    $estado = '';
            $estado_id = $existeAfiliado->estado_id;
    
            if ($estado_id == $pendiente) {
                $estado = 'Su proceso de afiliación está ' . $existeAfiliado->estado->detalle;
                $icono = 'alert-outline';
                $color = 'warning';
            } else if ($estado_id == $activo) {
                $estado = 'Su afiliación se encuentra ' . $existeAfiliado->estado->detalle;
                $icono = 'checkbox-outline';
                $color = 'success';
            }
    
            $response = ['afiliado' => true,'message' => $estado,'icono' => $icono,'color' => $color];
        }
        return response()->json($response);
    }


    public function guardarAfiliado(Request $request){
        $clienteRequest = (object)$request->cliente;
        $personaRequest = (object) $request->persona;
        $userRequest = (object) $request->user;
        $afiliadoRequest = (object) $request->afiliado;
        $contactoEmergenciaRequest = (object) $request->contacto_emergencia;
        $detalleAfiliadoRequest = (array) $request->detalle_afiliado;
        $response = [];

        $pendiente = 1;

        if($clienteRequest){
            $cliente = Cliente::find($clienteRequest->cliente_id);

            if($cliente){
                $cliente->persona->cedula = $personaRequest->cedula;
                $cliente->persona->nombres = $personaRequest->nombres;
                $cliente->persona->apellidos = $personaRequest->apellidos;
                $cliente->persona->celular = $personaRequest->celular;
                $cliente->persona->direccion = $personaRequest->direccion;
                $cliente->persona->estado = 'A'; 
                $cliente->persona->save();

                $personaId = $cliente->persona_id;
                $user = User::where('persona_id',$personaId)->get()->first();
                $user->rol;
                $user->persona;

                if($user){
                    $user->email = $userRequest->email;
                    $user->save();
                }

                //setear el afiliado
                $nuevoAfiliado = new Afiliado();
                $nuevoAfiliado->cliente_id = $afiliadoRequest->cliente_id;
                $nuevoAfiliado->estado_civil_id= $afiliadoRequest->estado_civil_id;
                $nuevoAfiliado->fecha= date('Y-m-d');
                $nuevoAfiliado->estado_id =  $pendiente;
                $nuevoAfiliado->facturado = 'N';

                if($nuevoAfiliado->save()){

                    $nuevoContactoEmergencia = new Contacto_Emergencia();
                    $nuevoContactoEmergencia->afiliado_id = $nuevoAfiliado->id;
                    $nuevoContactoEmergencia->parentesco_id = $contactoEmergenciaRequest->parentesco_id; 
                    $nuevoContactoEmergencia->nombre = $contactoEmergenciaRequest->nombre; 
                    $nuevoContactoEmergencia->num_celular = $contactoEmergenciaRequest->num_celular;
                    $nuevoContactoEmergencia->save();
                    
                    foreach($detalleAfiliadoRequest as $item){
                        $nuevoDetalleAfiliado = new Detalle_Afiliado();
                        $nuevoDetalleAfiliado->afiliado_id = $nuevoAfiliado->id;
                        $nuevoDetalleAfiliado->servicio_id = $item['servicio_id'];
                        $nuevoDetalleAfiliado->duracion_mes_id = $item['duracion_mes_id'];
                        $nuevoDetalleAfiliado->costo_mensual = $item['costo'];
                        $nuevoDetalleAfiliado->save();
                    }

                    $movil = 1;
                    $menu = $this->permisoCtrl->permisos($user->rol->id,$movil);

                    if($user->rol_id == 3){//Cliente
                        $user->persona->cliente;
                        $payload = ['user' => $user, 'menu' => $menu];
                    }
                    $token = JWTAuth::customClaims($payload)->fromUser($user);

                    $response = [
                        'status'=> true,
                        'message' => 'Se registro con éxito su afilicación',
                        'token' => $token
                    ];
                }else {
                    $response = [
                        'status'=> false,
                        'message' => 'No se puede registrar el afiliado'
                    ];
                }
            }else {
                $response = [
                    'status'=> false,
                    'message' => 'No existe el cliente'
                ];
            }
        }else {
            $response = [
                'status'=> false,
                'message' => 'No hay datos para procesar'
            ];
        }
        return response()->json($response);
    }


    public function tableAfiliado($estado_id){
        $afiliado = Afiliado::where('estado_id',intval($estado_id))->get();
        $response = [];

        if ($afiliado->count() > 0) {
            foreach($afiliado as $afi){
                $afi->cliente->persona;
                $afi->estado_civil;
                $afi->estado;

                foreach($afi->contacto_emergencia as $ce){
                    $ce->parentesco;
                }

                foreach($afi->detalle_afiliado as $da){
                    $da->servicio->categoria;
                    $da->duracion_mes;
                }
            }
            $response = [
                'status'=> true,
                'message' => 'existen datos',
                'data' => $afiliado
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
