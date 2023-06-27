<?php

namespace App\Http\Controllers;

use App\Models\Asignacion_Venta_Repartidor;
use App\Models\Repartidor;
use App\Models\Venta;
use Illuminate\Http\Request;

class Asignacion_Venta_RepartidorController extends Controller
{
    public function saveAsignacion(Request $request){
        $asignacionRequest = (object)$request->asignacion;
        $pedidosRequest = collect($asignacionRequest->pedidos_asignados);
        $response = [];

        $repartidor_id = intval($asignacionRequest->repartidor_id);

        if ($asignacionRequest) {
            $pedidosRequest->each(function ($pedido) use ($repartidor_id) {
                $venta_id = $pedido['id'];
        
                $newAsignacion = new Asignacion_Venta_Repartidor();
                $newAsignacion->repartidor_id = $repartidor_id;
                $newAsignacion->venta_id = $venta_id;
                $newAsignacion->save();
        
                $this->setDisponibilidadPedido($venta_id);
            });

            $dataRepartidor = Repartidor::find($repartidor_id);
            $dataRepartidor->disponible = 'N';
            $dataRepartidor->save();

            $response = [ 
                'status' => true, 
                'message' => 'Su pedido fue asignado al Sr. ' . $dataRepartidor->persona->nombres . ' ' .$dataRepartidor->persona->apellidos,  
            ];
        } else {
            $response = [ 'status' => false, 'message' => 'No hay datos para procesar' ];
        }
        return response()->json($response);
    }

    protected function setDisponibilidadPedido($venta_id){
        $venta = Venta::find($venta_id);
        $venta->asignado = 'S';
        $venta->save();
    }

    public function verPedidosAsignados($repartidor_id){

        $dataAsignacion = Asignacion_Venta_Repartidor::where('repartidor_id',$repartidor_id)->get();
        $response = [];

        if ($dataAsignacion->count() > 0) {
            foreach($dataAsignacion as $a){
                $venta = $a->venta;
                $venta->cliente->persona;

                foreach ($venta->venta_ubicacion as $vu) {
                    $vu->provincia;
                }

                foreach ($venta->detalle_venta as $dv) {
                    if (!is_null($dv->producto)) {
                        $dv->producto->categoria;
                    }

                    if (!is_null($dv->servicio)) {
                        $dv->servicio->categoria;
                    }
                }
            }
            $response = [ 'status' => true, 'message' => 'existen datos', 'data' => $dataAsignacion];    
        } else {
            $response = [ 'status' => false, 'message' => 'No hay datos para procesar' ];  
        }
        return response()->json($response);
    }

    public function pedidosEntregado($asignacion_venta_repartidor_id, $repartidor_id){
        $dataAsignacion = Asignacion_Venta_Repartidor::find($asignacion_venta_repartidor_id);
        $response = [];   $entregado = 2;

        if ($dataAsignacion) {
            $dataVenta = Venta::find(intval($dataAsignacion->venta_id));
            //setear la fecha y hora de entrega
            $dataVenta->estado_id = $entregado;
            $dataVenta->fecha_hora_entrega = date('Y-m-d H:i:s');
            $dataVenta->save();

            //eliminar la relacion del pedido de cd repartidor
            $dataAsignacion->delete();

            $ultimoRepartidor = Asignacion_Venta_Repartidor::where('repartidor_id', intval($dataAsignacion->repartidor_id))->get();

            if (count($ultimoRepartidor) == 0) {//validar bien
                // El array está vacío "le hacemos disponible al repartidor"
                $repartidor = Repartidor::find(intval($repartidor_id));
                $repartidor->disponible = 'S';
                $repartidor->save();
            }

            $response = [ 
                'status' => true, 
                'message' => 'El pedido ah sido entregado '. $dataVenta->cliente->persona->nombres . ' ' .$dataVenta->cliente->persona->apellidos, 
                // 'data' => $dataAsignacion,
                // 'venta' => $dataVenta,
                // 'ultimo_repartidor' => $ultimoRepartidor
            ];     
        } else {
            $response = [ 'status' => false, 'message' => 'No hay datos para procesar' ];     
        }
        return response()->json($response);
    }
}
