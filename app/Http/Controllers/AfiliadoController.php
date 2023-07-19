<?php

namespace App\Http\Controllers;

use App\Models\Afiliado;
use App\Models\Cliente;
use App\Models\Contacto_Emergencia;
use App\Models\Detalle_Afiliado;
use App\Models\Duracion_Mes;
use App\Models\Fecha_Pagos;
use App\Models\Servicios;
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

    public function verificacionAfiliacionReturnServicioSoloPlan($cliente_id){
        $existeAfiliado = Afiliado::where('cliente_id',$cliente_id)->get()->first();
        $response = [];

        if (!$existeAfiliado) {

            $planes = 3;
            $servicio = Servicios::where('estado','A')->where('categoria_id',$planes)->get();

            $response = [ 'status'=> false,'message' => 'No se encuentra afiliado', 'data' => $servicio];
        }else {

            $afiliadoServicio = $existeAfiliado->detalle_afiliado;
            $existingServices = [];

            foreach ($afiliadoServicio as $as) {
                $existeQueNoEstaPagado = Fecha_Pagos::where('afiliado_id', $existeAfiliado->id)
                    ->where('servicio_id', $as['servicio_id'])
                    ->where('isPagado', 'N')
                    ->first();

                if ($existeQueNoEstaPagado) {
                    $existingServices[] = $as['servicio_id'];
                }
            }

            $planes = 3;

            $servicio = Servicios::where('estado', 'A')
                ->where('categoria_id', $planes)
                ->whereNotIn('id', $existingServices)
                ->get();

            $response = [
                'status' => true,
                'message' => 'hay datos',
                'data' => $servicio
            ];
    }
        return response()->json($response);

    }


    public function verificarAfiliacion($cliente_id){
        $existeAfiliado = Afiliado::where('cliente_id',$cliente_id)->get()->first();
        $response = [];
       
        if (!$existeAfiliado) {
            $response = [ 'afiliado'=> false,'message' => 'No se encuentra afiliado','icono' => 'alert-outline','color' =>'danger'];
        }else {
            $pendiente = 1;    $activo = 4;  $anulado = 3;  $estado = '';
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
            else if ($estado_id == $anulado) {
                $estado = 'Su afiliación se encuentra ' . $existeAfiliado->estado->detalle;
                $icono = 'close-circle-outline';
                $color = 'info';
                 return $response = [ 'afiliado'=> false,'message' => 'Se encuentra afiliado, pero su servicios han sido eliminados','icono' => 'alert-outline','color' =>'danger'];
            }
    
            $response = ['afiliado' => true,'message' => $estado,'icono' => $icono,'color' => $color];
        }
        return response()->json($response);
    }


    /* public function guardarAfiliadoAnterior(Request $request){
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

                //validar si exite el cliente y verificar si es anulado 
                //borrar su detalle afiliado 

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
    } */

    public function guardarAfiliado2(Request $request){
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

                $existeAfiliadoCliente = Afiliado::where('cliente_id',$afiliadoRequest->cliente_id)->get()->first();

                if ($existeAfiliadoCliente) {// ya no guarda afiliado sino que recuperamos el id del afiliado

                    $existeAfiliadoCliente->estado_id = $pendiente;
                    $existeAfiliadoCliente->save();

                    $nuevoContactoEmergencia = new Contacto_Emergencia();
                    $nuevoContactoEmergencia->afiliado_id = $existeAfiliadoCliente->id;
                    $nuevoContactoEmergencia->parentesco_id = $contactoEmergenciaRequest->parentesco_id; 
                    $nuevoContactoEmergencia->nombre = $contactoEmergenciaRequest->nombre; 
                    $nuevoContactoEmergencia->num_celular = $contactoEmergenciaRequest->num_celular;
                    $nuevoContactoEmergencia->save();

                    foreach($detalleAfiliadoRequest as $item){
                        $nuevoDetalleAfiliado = new Detalle_Afiliado();
                        $nuevoDetalleAfiliado->afiliado_id = $existeAfiliadoCliente->id;
                        $nuevoDetalleAfiliado->servicio_id = $item['servicio_id'];
                        $nuevoDetalleAfiliado->duracion_mes_id = $item['duracion_mes_id'];
                        $nuevoDetalleAfiliado->costo_mensual = $item['costo'];

                        $duracionMes = Duracion_Mes::find(intval($nuevoDetalleAfiliado->duracion_mes_id));

                            if ( $duracionMes ) {
                                $fechaInicio = $existeAfiliadoCliente->fecha;
                                $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaInicio)));
                                //generar las fechas de pagos automatico sumando 1 mes
                                for ($i=0; $i < $duracionMes->duracion ; $i++) { 
                                    $nuevaFechaPago = new Fecha_Pagos();
                                    $nuevaFechaPago->afiliado_id = intval($nuevoDetalleAfiliado->afiliado_id);
                                    $nuevaFechaPago->servicio_id = intval( $nuevoDetalleAfiliado->servicio_id);
                                    $nuevaFechaPago->fecha_pago = $fechaPago;
                                    $nuevaFechaPago->save();
                                    $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaPago)));
                                }
                            }
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
                        'message' => 'Se registro con éxito sus servicios',
                        'token' => $token
                    ];
                }else {
                    //setear el afiliado
                    $nuevoAfiliado = new Afiliado();
                    $nuevoAfiliado->cliente_id = $afiliadoRequest->cliente_id;
                    $nuevoAfiliado->estado_civil_id= $afiliadoRequest->estado_civil_id;
                    $nuevoAfiliado->fecha = date('Y-m-d');
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

                            $duracionMes = Duracion_Mes::find(intval($nuevoDetalleAfiliado->duracion_mes_id));

                            if ( $duracionMes ) {
                                $fechaInicio = $nuevoAfiliado->fecha;
                                $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaInicio)));
                                //generar las fechas de pagos automatico sumando 1 mes
                                for ($i=0; $i < $duracionMes->duracion ; $i++) { 
                                    $nuevaFechaPago = new Fecha_Pagos();
                                    $nuevaFechaPago->afiliado_id = intval($nuevoDetalleAfiliado->afiliado_id);
                                    $nuevaFechaPago->servicio_id = intval( $nuevoDetalleAfiliado->servicio_id);
                                    $nuevaFechaPago->fecha_pago = $fechaPago;
                                    $nuevaFechaPago->save();
                                    $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaPago)));
                                }
                            }
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

    public function guardarAfiliadoConsumido(Request $request){
        $clienteRequest = (object)$request->cliente;
        $personaRequest = (object) $request->persona;
        $userRequest = (object) $request->user;
        $afiliadoRequest = (object) $request->afiliado;
        $contactoEmergenciaRequest = (object) $request->contacto_emergencia;
        $detalleAfiliadoRequest = (array) $request->detalle_afiliado;
        $response = [];

        $pendiente = 1;

        if($clienteRequest){//primer registro
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

                $existeAfiliadoCliente = Afiliado::where('cliente_id',$afiliadoRequest->cliente_id)->get()->first();

                if ($existeAfiliadoCliente) {// ya no guarda afiliado sino que recuperamos el id del afiliado

                    $existeAfiliadoCliente->estado_id = $pendiente;
                    $existeAfiliadoCliente->save();

                    // ya no guarda en contacto sino que actualizamos su info del contacto de emergencia
                    $existeContactoEmergencia = Contacto_Emergencia::where('afiliado_id', $existeAfiliadoCliente->id)->get()->first();
                    $existeContactoEmergencia->parentesco_id = $contactoEmergenciaRequest->parentesco_id; 
                    $existeContactoEmergencia->nombre = $contactoEmergenciaRequest->nombre; 
                    $existeContactoEmergencia->num_celular = $contactoEmergenciaRequest->num_celular;
                    $existeContactoEmergencia->save();

                    foreach($detalleAfiliadoRequest as $item){
                        $nuevoDetalleAfiliado = new Detalle_Afiliado();
                        $nuevoDetalleAfiliado->afiliado_id = $existeAfiliadoCliente->id;
                        $nuevoDetalleAfiliado->servicio_id = $item['servicio_id'];
                        $nuevoDetalleAfiliado->duracion_mes_id = $item['duracion_mes_id'];
                        $nuevoDetalleAfiliado->costo_mensual = $item['costo'];

                        $duracionMes = Duracion_Mes::find(intval($nuevoDetalleAfiliado->duracion_mes_id));

                            if ( $duracionMes ) {
                                $fechaInicio = $existeAfiliadoCliente->fecha;
                                $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaInicio)));
                                //generar las fechas de pagos automatico sumando 1 mes
                                for ($i=0; $i < $duracionMes->duracion ; $i++) { 
                                    $nuevaFechaPago = new Fecha_Pagos();
                                    $nuevaFechaPago->afiliado_id = intval($nuevoDetalleAfiliado->afiliado_id);
                                    $nuevaFechaPago->servicio_id = intval( $nuevoDetalleAfiliado->servicio_id);
                                    $nuevaFechaPago->fecha_pago = $fechaPago;
                                    $nuevaFechaPago->save();
                                    $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaPago)));
                                }
                            }
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
                        'message' => 'Se registro con éxito sus servicios',
                        'token' => $token
                    ];
                }else {
                    //setear el afiliado
                    $nuevoAfiliado = new Afiliado();
                    $nuevoAfiliado->cliente_id = $afiliadoRequest->cliente_id;
                    $nuevoAfiliado->estado_civil_id= $afiliadoRequest->estado_civil_id;
                    $nuevoAfiliado->fecha = date('Y-m-d');
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

                            $duracionMes = Duracion_Mes::find(intval($nuevoDetalleAfiliado->duracion_mes_id));

                            if ( $duracionMes ) {
                                $fechaInicio = $nuevoAfiliado->fecha;
                                $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaInicio)));
                                //generar las fechas de pagos automatico sumando 1 mes
                                for ($i=0; $i < $duracionMes->duracion ; $i++) { 
                                    $nuevaFechaPago = new Fecha_Pagos();
                                    $nuevaFechaPago->afiliado_id = intval($nuevoDetalleAfiliado->afiliado_id);
                                    $nuevaFechaPago->servicio_id = intval( $nuevoDetalleAfiliado->servicio_id);
                                    $nuevaFechaPago->fecha_pago = $fechaPago;
                                    $nuevaFechaPago->save();
                                    $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaPago)));
                                }
                            }
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

    public function guardarAfiliado(Request $request){
        $clienteRequest = (object)$request->cliente;
        $personaRequest = (object) $request->persona;
        $userRequest = (object) $request->user;
        $afiliadoRequest = (object) $request->afiliado;
        $contactoEmergenciaRequest = (object) $request->contacto_emergencia;
        $detalleAfiliadoRequest = (array) $request->detalle_afiliado;
        $response = [];

        $pendiente = 1; $activo = 4;

        if($clienteRequest){//primer registro
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

                $existeAfiliadoCliente = Afiliado::where('cliente_id',$afiliadoRequest->cliente_id)->get()->first();

                if ($existeAfiliadoCliente) {// ya no guarda afiliado sino que recuperamos el id del afiliado

                    $existeAfiliadoCliente->cliente_id = $afiliadoRequest->cliente_id;
                    $existeAfiliadoCliente->estado_civil_id= $afiliadoRequest->estado_civil_id;
                    $existeAfiliadoCliente->fecha = date('Y-m-d');
                    $existeAfiliadoCliente->estado_id =  $activo;
                    $existeAfiliadoCliente->facturado = 'N';

                    if($existeAfiliadoCliente->save()){

                        $existeContactoEmergencia = Contacto_Emergencia::where('afiliado_id',$existeAfiliadoCliente->id)->get()->first();
                        $existeContactoEmergencia->afiliado_id = $existeAfiliadoCliente->id;
                        $existeContactoEmergencia->parentesco_id = $contactoEmergenciaRequest->parentesco_id; 
                        $existeContactoEmergencia->nombre = $contactoEmergenciaRequest->nombre; 
                        $existeContactoEmergencia->num_celular = $contactoEmergenciaRequest->num_celular;
                        $existeContactoEmergencia->save();
                        
                        foreach($detalleAfiliadoRequest as $item){
                            $nuevoDetalleAfiliado = new Detalle_Afiliado();
                            $nuevoDetalleAfiliado->afiliado_id = $existeAfiliadoCliente->id;
                            $nuevoDetalleAfiliado->servicio_id = $item['servicio_id'];
                            $nuevoDetalleAfiliado->duracion_mes_id = $item['duracion_mes_id'];
                            $nuevoDetalleAfiliado->costo_mensual = $item['costo'];

                            $duracionMes = Duracion_Mes::find(intval($nuevoDetalleAfiliado->duracion_mes_id));

                            if ( $duracionMes ) {
                                $fechaInicio = $existeAfiliadoCliente->fecha;
                                $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaInicio)));
                                //generar las fechas de pagos automatico sumando 1 mes
                                for ($i=0; $i < $duracionMes->duracion ; $i++) { 
                                    $nuevaFechaPago = new Fecha_Pagos();
                                    $nuevaFechaPago->afiliado_id = intval($nuevoDetalleAfiliado->afiliado_id);
                                    $nuevaFechaPago->servicio_id = intval( $nuevoDetalleAfiliado->servicio_id);
                                    $nuevaFechaPago->fecha_pago = $fechaPago;
                                    $nuevaFechaPago->isPagado = 'N';
                                    $nuevaFechaPago->save();
                                    $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaPago)));
                                }
                            }
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
                    //setear el afiliado nuevo
                    $nuevoAfiliado = new Afiliado();
                    $nuevoAfiliado->cliente_id = $afiliadoRequest->cliente_id;
                    $nuevoAfiliado->estado_civil_id= $afiliadoRequest->estado_civil_id;
                    $nuevoAfiliado->fecha = date('Y-m-d');
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

                            $duracionMes = Duracion_Mes::find(intval($nuevoDetalleAfiliado->duracion_mes_id));

                            if ( $duracionMes ) {
                                $fechaInicio = $nuevoAfiliado->fecha;
                                $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaInicio)));
                                //generar las fechas de pagos automatico sumando 1 mes
                                for ($i=0; $i < $duracionMes->duracion ; $i++) { 
                                    $nuevaFechaPago = new Fecha_Pagos();
                                    $nuevaFechaPago->afiliado_id = intval($nuevoDetalleAfiliado->afiliado_id);
                                    $nuevaFechaPago->servicio_id = intval( $nuevoDetalleAfiliado->servicio_id);
                                    $nuevaFechaPago->fecha_pago = $fechaPago;
                                    $nuevaFechaPago->isPagado = 'N';
                                    $nuevaFechaPago->save();
                                    $fechaPago = date('Y-m-d', strtotime('+1 month', strtotime($fechaPago)));
                                }
                            }
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


    // public function guardarOtroPlanAfiliado(Request $request){
    //     $clienteRequest = (object)$request->cliente;

    // }


    public function tableAfiliado($estado_id){
        $afiliado = Afiliado::where('estado_id',intval($estado_id))->get();
        $response = [];

        if ($afiliado->count() > 0) {
            foreach($afiliado as $afi){
                $afi->cliente->persona->user;
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

    public function cambioEstadoG($afiliado_id,$estado_id){
        $afiliado = Afiliado::find(intval($afiliado_id));

        $nombreCliente = $afiliado->cliente->persona->nombres . ' ' . $afiliado->cliente->persona->apellidos;
        $response = [];

        if ($afiliado) {
            $afiliado->estado_id = intval($estado_id);
            $afiliado->save();

            $response = [
                'status' => true,
                'message' => $nombreCliente . ' ha sido '. $afiliado->estado->detalle
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'No existe datos'
            ];
        }
        return response()->json($response);
    }

    public function cambioEstado($afiliado_id,$estado_id){
        $afiliado = Afiliado::find(intval($afiliado_id));

        $nombreCliente = $afiliado->cliente->persona->nombres . ' ' . $afiliado->cliente->persona->apellidos;
        $response = [];

        if ($afiliado) {
            $afiliado->estado_id = intval($estado_id);
            $afiliado->save();

            $anulado = 3;
            if ($afiliado->estado_id === $anulado) {
                //si es anulado eliminamos el detalle de su afilicion
                /* foreach($afiliado->detalle_afiliado as $da){
                    $afiliado_id = $da->afiliado_id;
                    $detalleAfilicacion =  Detalle_Afiliado::where('afiliado_id',$afiliado_id)->get();
                    $detalleAfilicacion->delete();
                } */
                $afiliado->detalle_afiliado()->pluck('id')->each(function ($id) {
                    Detalle_Afiliado::destroy($id);
                });

                $afiliado->contacto_emergencia()->pluck('id')->each(function ($id) {
                    Contacto_Emergencia::destroy($id);
                });

                $afiliado->fecha_pago()->pluck('id')->each(function ($id) {
                    Fecha_Pagos::destroy($id);
                });
            }

            $response = [
                'status' => true,
                'message' => $nombreCliente . ' ha sido '. $afiliado->estado->detalle
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'No existe datos'
            ];
        }
        return response()->json($response);
    }

    public function cantidadAfiliados(){
        $activos = 4;  $anuladosInactivos = 3;
        $afiliadosActivos = Afiliado::where('estado_id',$activos)->get();
        $afiliadosInactivos = Afiliado::where('estado_id',$anuladosInactivos)->get();

        if (count($afiliadosActivos) > 0 || count($afiliadosInactivos) > 0 ) {
            $response = [
                'status' => true,
                'message' => 'existe datos',
                'data' => [
                    'nombre' => 'Afiliados',
                    'cantidad_activos' => $afiliadosActivos->count(),
                    'cantidad_inactivos' => $afiliadosInactivos->count(),
                ] 
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'no existe datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }

    public function mostrarAfiliadosActivos(){
        $activos = 4;  $response = [];
        $afiliados = Afiliado::where('estado_id',$activos)->get();

        if (count($afiliados) > 0 ) {
            foreach($afiliados as $afi){
                $afi->cliente->persona;
            }
            $response = [
                'status' => true,
                'message' => 'existe datos',
                'data' => $afiliados
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'no existe datos',
                'data' => null
            ];
        }
        return response()->json($response);
    }
}
