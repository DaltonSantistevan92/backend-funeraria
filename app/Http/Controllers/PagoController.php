<?php

namespace App\Http\Controllers;

use App\Models\Detalle_Pago;
use App\Models\Fecha_Pagos;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    //

    public function pagosPendientes()
    {
        // // Obtener el ID del afiliado
        // $afiliadoId = 9; // Ejemplo, debes reemplazarlo por el ID real del afiliado

        // // Realizar la consulta para verificar los pagos pendientes
        // $pagosPendientes = DB::table('afiliados')
        //     ->join('detalle_afiliado', 'afiliados.id', '=', 'detalle_afiliado.afiliado_id')
        //     ->leftJoin('pagos', function ($join) use ($afiliadoId) {
        //         $join->on('detalle_afiliado.afiliado_id', '=', 'pagos.afiliado_id')
        //             ->on('detalle_afiliado.servicio_id', '=', 'pagos.servicio_id')
        //             ->on('detalle_afiliado.duracion_mes_id', '=', 'pagos.fecha')
        //             ->where('pagos.afiliado_id', '=', $afiliadoId);
        //     })
        //     ->whereNull('pagos.id')
        //     ->select('afiliados.id', 'detalle_afiliado.servicio_id', 'detalle_afiliado.duracion_mes_id')
        //     ->get();

        // // Verificar si existen pagos pendientes
        // if ($pagosPendientes->isEmpty()) {
        //     echo "No tienes pagos pendientes.";
        // } else {
        //     echo "Tienes pagos pendientes por los siguientes servicios y duraciones:";
        //     foreach ($pagosPendientes as $pagoPendiente) {
        //         echo "Servicio ID: " . $pagoPendiente->servicio_id . ", Duración Mes ID: " . $pagoPendiente->duracion_mes_id . "\n";
        //     }
        // }

        // // Obtener el ID del afiliado
        // $afiliadoId = 9; // Ejemplo, debes reemplazarlo por el ID real del afiliado

        // // Realizar la consulta para obtener las letras pendientes de pago
        // $letrasPendientes = DB::table('afiliados')
        //     ->join('detalle_afiliado', 'afiliados.id', '=', 'detalle_afiliado.afiliado_id')
        //     ->leftJoin('pagos', function ($join) use ($afiliadoId) {
        //         $join->on('detalle_afiliado.afiliado_id', '=', 'pagos.afiliado_id')
        //             ->on('detalle_afiliado.servicio_id', '=', 'pagos.servicio_id')
        //             ->where('pagos.afiliado_id', '=', $afiliadoId);
        //     })
        //     ->where(function ($query) {
        //         $query->whereNull('pagos.id')
        //             ->orWhere('detalle_afiliado.duracion_mes_id', '>', DB::raw('MONTH(pagos.fecha)'));
        //     })
        //     ->select('detalle_afiliado.duracion_mes_id')
        //     ->get();

        //     return response()->json($letrasPendientes); die();

        // // Generar el reporte de las letras pendientes
        // echo "Letras pendientes de pago:\n";
        // foreach ($letrasPendientes as $letra) {
        //     $letraActual = $letra->duracion_mes_id;
        //     $letraFinal = $letraActual + $letra->duracion - 1;
        //     echo "Letra: $letraActual - $letraFinal\n";
        // }

    }

    public function savePagos(Request $request)
    {

        
        $pagosRequest = (object) $request->pagos;
        $deallePagosRequest = (array) $request->detalle_pago;
        $response = [];
        
        //return response()->json($request); die();
        if ($pagosRequest) {
            // Verificar si el pago ya existe en la base de datos
            $existePago = Pago::where('afiliado_id', intval($pagosRequest->afiliado_id))->get()->first();

            if ($existePago) {
                foreach ($deallePagosRequest as $dp) {
                    $newDetallePago = new Detalle_Pago();
                    $newDetallePago->pago_id = $existePago->id;
                    $newDetallePago->servicio_id = $dp['servicio_id'];
                    $newDetallePago->mes = $dp['mes'];
                    $newDetallePago->fecha_pago = date('Y-m-d');
                    $newDetallePago->total_pagado = $dp['total_pagado'];
                    $newDetallePago->save();

                    $afiliado_id =  intval($pagosRequest->afiliado_id);
                    $servicio_id = $newDetallePago->servicio_id;
                    $mes = $newDetallePago->mes;

                    $this->updateLetrasPagada($afiliado_id,$servicio_id,$mes);
                }
            } else {
                $newPago = new Pago();
                $newPago->afiliado_id = intval($pagosRequest->afiliado_id);
                $newPago->status = 'A';

                if ($newPago->save()) {
                    foreach ($deallePagosRequest as $dp) {
                        $newDetallePago = new Detalle_Pago();
                        $newDetallePago->pago_id = $newPago->id;
                        $newDetallePago->servicio_id = $dp['servicio_id'];
                        $newDetallePago->mes = $dp['mes'];
                        $newDetallePago->fecha_pago = date('Y-m-d');
                        $newDetallePago->total_pagado = $dp['total_pagado'];
                        $newDetallePago->save();

                        $afiliado_id = $newPago->afiliado_id;
                        $servicio_id = $newDetallePago->servicio_id;
                        $mes = $newDetallePago->mes;

                        $this->updateLetrasPagada($afiliado_id,$servicio_id,$mes);
                    }
                }
            }
            $response = ['status' => true, 'message' => 'Su pago se realizó con éxito'];
        } else {
            $response = ['status' => false, 'message' => 'no existe datos'];
        }
        return response()->json($response);
    }

    private function updateLetrasPagada($afiliado_id, $servicio_id, $mes) {
        $fechaPagos = Fecha_Pagos::where('afiliado_id', $afiliado_id)
                                ->where('servicio_id', $servicio_id)
                                ->where('isPagado', 'N')
                                ->orderBy('id')
                                ->take($mes)
                                ->get();
    
        if ($fechaPagos->isNotEmpty()) { //para verificar si hay fechas de pago disponibles antes de realizar la actualización
            foreach ($fechaPagos as $fechaPago) {
                $fechaPago->isPagado = 'S';
                $fechaPago->save();
            }
        }
    }

    public function savePago2(Request $request)
    {
        $pagosRequest = (object) $request->pagos;
        $deallePagosRequest = (array) $request->detalle_pago;
        $response = [];

        if ($pagosRequest) {
            // Verificar si el pago ya existe en la base de datos
            $existePago = Pago::where('afiliado_id', intval($pagosRequest->afiliado_id))->get()->first();

            if ($existePago) {
                foreach ($deallePagosRequest as $dp) {
                    $newDetallePago = new Detalle_Pago();
                    $newDetallePago->pago_id = $existePago->id;
                    $newDetallePago->servicio_id = $dp['servicio_id'];
                    $newDetallePago->mes = $dp['mes'];
                    $newDetallePago->fecha_pago = date('Y-m-d');
                    $newDetallePago->total_pagado = $dp['total_pagado'];
                    $newDetallePago->save();
                }
            } else {
                $newPago = new Pago();
                $newPago->afiliado_id = intval($pagosRequest->afiliado_id);
                $newPago->status = 'A';

                if ($newPago->save()) {
                    foreach ($deallePagosRequest as $dp) {
                        $newDetallePago = new Detalle_Pago();
                        $newDetallePago->pago_id = $newPago->id;
                        $newDetallePago->servicio_id = $dp['servicio_id'];
                        $newDetallePago->mes = $dp['mes'];
                        $newDetallePago->fecha_pago = date('Y-m-d');
                        $newDetallePago->total_pagado = $dp['total_pagado'];
                        $newDetallePago->save();
                    }
                }
            }
            $response = ['status' => true, 'message' => 'Su pago se realizó con éxito'];
        } else {
            $response = ['status' => false, 'message' => 'no existe datos'];
        }
        return response()->json($response);
    }

    public function obtenerInformacionAfiliadoOrTodos2($afiliadoIdOrTodos)
    {
        $todos = -1;
        $response = [];

        $baseQuery = DB::table('afiliados')
            ->selectRaw(
                "afiliados.id AS afiliado_id,
                CONCAT(personas.nombres, ' ', personas.apellidos) AS cliente,
                servicios.id AS servicio_id,
                servicios.nombre AS servicio,
                MAX(servicios.precio) AS precio_servicio,
                detalle_afiliado.costo_mensual AS monto_mensual,
                duracion_meses.duracion AS duracion_meses,
                COALESCE(SUM(detalle_pago.mes), 0) AS letras_pagadas,
                duracion_meses.duracion - COALESCE(SUM(detalle_pago.mes), 0) AS letras_pendientes,
                CASE WHEN COALESCE(((servicios.precio) - COALESCE(SUM(detalle_pago.total_pagado), 0)), 0) <= 0 THEN 0
                ELSE ROUND(COALESCE(((servicios.precio) - COALESCE(SUM(detalle_pago.total_pagado), 0)), 0), 2) END AS monto_pendiente,
                ROUND(COALESCE(SUM(detalle_pago.total_pagado),0),2) AS monto_pagado"
            )
            ->join('clientes', 'afiliados.cliente_id', '=', 'clientes.id')
            ->join('personas', 'personas.id', '=', 'clientes.persona_id')
            ->join('detalle_afiliado', 'afiliados.id', '=', 'detalle_afiliado.afiliado_id')
            ->join('duracion_meses', 'detalle_afiliado.duracion_mes_id', '=', 'duracion_meses.id')
            ->join('servicios', 'detalle_afiliado.servicio_id', '=', 'servicios.id')
            ->leftJoin('pagos', 'afiliados.id', '=', 'pagos.afiliado_id')
            ->leftJoin('detalle_pago', function ($join) {
                $join->on('pagos.id', '=', 'detalle_pago.pago_id')
                    ->on('detalle_afiliado.servicio_id', '=', 'detalle_pago.servicio_id');
            })
            ->groupBy('afiliados.id', 
                    'personas.nombres', 
                    'personas.apellidos', 
                    'servicios.id', 
                    'servicios.nombre', 
                    'servicios.precio', 
                    'detalle_afiliado.costo_mensual', 
                    'duracion_meses.duracion')
            ->orderBy('afiliados.id', 'asc');

        if ($afiliadoIdOrTodos == $todos) {
            $afiliados = $baseQuery->get();

            if (count($afiliados) > 0) {
                
                $response = ['status' => true, 'message' => 'existen datos', 'data' => $afiliados];
            } else {
                $response = ['status' => false, 'message' => 'no existen datos', 'data' => null];
            }
        } else {
            $afiliados = $baseQuery->where('afiliados.id', $afiliadoIdOrTodos)->get();

            if (count($afiliados) > 0) {
                $response = ['status' => true, 'message' => 'existen datos', 'data' => $afiliados];
            } else {
                $response = ['status' => false, 'message' => 'no existen datos', 'data' => null];
            }
        }

        return response()->json($response);
    }

    public function obtenerInformacionAfiliadoOrTodos($afiliadoIdOrTodos)
    {
        $todos = -1;
        $response = [];

        $baseQuery = DB::table('afiliados')
            ->selectRaw(
                "afiliados.id AS afiliado_id,
                CONCAT(personas.nombres, ' ', personas.apellidos) AS cliente,
                servicios.id AS servicio_id,
                servicios.nombre AS servicio,
                MAX(servicios.precio) AS precio_servicio,
                detalle_afiliado.costo_mensual AS monto_mensual,
                duracion_meses.duracion AS duracion_meses,
                COALESCE(SUM(detalle_pago.mes), 0) AS letras_pagadas,
                duracion_meses.duracion - COALESCE(SUM(detalle_pago.mes), 0) AS letras_pendientes,
                CASE WHEN COALESCE(((servicios.precio) - COALESCE(SUM(detalle_pago.total_pagado), 0)), 0) <= 0 THEN 0
                ELSE ROUND(COALESCE(((servicios.precio) - COALESCE(SUM(detalle_pago.total_pagado), 0)), 0), 2) END AS monto_pendiente,
                ROUND(COALESCE(SUM(detalle_pago.total_pagado),0),2) AS monto_pagado"
            )
            ->join('clientes', 'afiliados.cliente_id', '=', 'clientes.id')
            ->join('personas', 'personas.id', '=', 'clientes.persona_id')
            ->join('detalle_afiliado', 'afiliados.id', '=', 'detalle_afiliado.afiliado_id')
            ->join('duracion_meses', 'detalle_afiliado.duracion_mes_id', '=', 'duracion_meses.id')
            ->join('servicios', 'detalle_afiliado.servicio_id', '=', 'servicios.id')
            ->leftJoin('pagos', 'afiliados.id', '=', 'pagos.afiliado_id')
            ->leftJoin('detalle_pago', function ($join) {
                $join->on('pagos.id', '=', 'detalle_pago.pago_id')
                    ->on('detalle_afiliado.servicio_id', '=', 'detalle_pago.servicio_id');
            })
            ->groupBy('afiliados.id', 
                    'personas.nombres', 
                    'personas.apellidos', 
                    'servicios.id', 
                    'servicios.nombre', 
                    'servicios.precio', 
                    'detalle_afiliado.costo_mensual', 
                    'duracion_meses.duracion')
            ->orderBy('afiliados.id', 'asc');

        if ($afiliadoIdOrTodos == $todos) {
            $afiliados = $baseQuery->get();

            if (count($afiliados) > 0) {
                $result = [];

                foreach ($afiliados as $afiliado) {
                    $fechaPagos = Fecha_Pagos::where('afiliado_id', $afiliado->afiliado_id)
                                                ->where('servicio_id', $afiliado->servicio_id)
                                                ->get();
            
                    $data = [
                        'afiliado_id' => $afiliado->afiliado_id,
                        'cliente' => $afiliado->cliente,
                        'servicio_id' => $afiliado->servicio_id,
                        'servicio' => $afiliado->servicio,
                        'precio_servicio' => $afiliado->precio_servicio,
                        'monto_mensual' => $afiliado->monto_mensual,
                        'duracion_meses' => $afiliado->duracion_meses,
                        'letras_pagadas' => $afiliado->letras_pagadas,
                        'letras_pendientes' => $afiliado->letras_pendientes,
                        'monto_pendiente' => $afiliado->monto_pendiente,
                        'monto_pagado' => $afiliado->monto_pagado,
                        'fecha_pagos' => $fechaPagos
                    ];
            
                    $result[] = $data;
                }
                $response = ['status' => true, 'message' => 'existen datos', 'data' => $result];
            } else {
                $response = ['status' => false, 'message' => 'no existen datos', 'data' => null];
            }
        } else {
            $afiliados = $baseQuery->where('afiliados.id', $afiliadoIdOrTodos)->get();

            if (count($afiliados) > 0) {

                $result = [];

                foreach ($afiliados as $afiliado) {
                    $fechaPagos = Fecha_Pagos::where('afiliado_id', $afiliado->afiliado_id)
                                                ->where('servicio_id', $afiliado->servicio_id)
                                                ->get();
            
                    $data = [
                        'afiliado_id' => $afiliado->afiliado_id,
                        'cliente' => $afiliado->cliente,
                        'servicio_id' => $afiliado->servicio_id,
                        'servicio' => $afiliado->servicio,
                        'precio_servicio' => $afiliado->precio_servicio,
                        'monto_mensual' => $afiliado->monto_mensual,
                        'duracion_meses' => $afiliado->duracion_meses,
                        'letras_pagadas' => $afiliado->letras_pagadas,
                        'letras_pendientes' => $afiliado->letras_pendientes,
                        'monto_pendiente' => $afiliado->monto_pendiente,
                        'monto_pagado' => $afiliado->monto_pagado,
                        'fecha_pagos' => $fechaPagos
                    ];
            
                    $result[] = $data;
                }
                $response = ['status' => true, 'message' => 'existen datos', 'data' => $result ];
            } else {
                $response = ['status' => false, 'message' => 'no existen datos', 'data' => null];
            }
        }

        return response()->json($response);
    }

// public function pagoTableAfiliado($afiliado_id){
//     $afiliados = Afiliado::find(intval($afiliado_id));
//     $response = [];

//     if ($afiliados) {
//         $response = [ 'status' => true, 'message' => 'existen datos', 'data' => $afiliados ];
//     } else {
//         $response = [ 'status' => false, 'message' => 'no existen datos', 'data' => null ];
//     }

//     return response()->json($response);
// }

}
