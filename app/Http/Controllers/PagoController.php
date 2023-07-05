<?php

namespace App\Http\Controllers;

use App\Models\Afiliado;
use App\Models\Detalle_Pago;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    //


    public function pagosPendientes(){
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

    public function savePagos(Request $request){
        $pagosRequest = (object) $request->pagos;
        $deallePagosRequest = (array) $request->detalle_pago;
        $response = [];

        if ($pagosRequest) {
            // Verificar si el pago ya existe en la base de datos
            $existePago = Pago::where('afiliado_id', intval($pagosRequest->afiliado_id))->get()->first();

            if ($existePago) {
                foreach($deallePagosRequest as $dp){
                    $newDetallePago = new Detalle_Pago();
                    $newDetallePago->pago_id = $existePago->id;
                    $newDetallePago->servicio_id = $dp['servicio_id'];
                    $newDetallePago->mes = $dp['mes'];
                    $newDetallePago->fecha_pago = date('Y-m-d');
                    $newDetallePago->save();
               }
            } else {
                $newPago = new Pago();
                $newPago->afiliado_id = intval($pagosRequest->afiliado_id);
                $newPago->monto = doubleval($pagosRequest->monto);

                if ($newPago->save()) {
                    foreach($deallePagosRequest as $dp){
                         $newDetallePago = new Detalle_Pago();
                         $newDetallePago->pago_id = $newPago->id;
                         $newDetallePago->servicio_id = $dp['servicio_id'];
                         $newDetallePago->mes = $dp['mes'];
                         $newDetallePago->fecha_pago = date('Y-m-d');
                         $newDetallePago->save();
                    }
                }
            }
            $response = ['status' => true, 'message' => 'se actualizo su pago' ];
        } else {
            $response = ['status' => false, 'message' => 'no existe datos' ];
        }
        return response()->json($response);
    }


    public function obtenerInformacionAfiliadoOrTodos($afiliadoIdOrTodos)//REPORTE DE TODOS LOS AFILIADOS 
    {

        $todos = -1;  $response = [];
        if ($afiliadoIdOrTodos == $todos) {

            $afiliados = DB::table('afiliados')
            ->selectRaw(
                "afiliados.id AS afiliado_id,
                CONCAT(personas.nombres, ' ', personas.apellidos) AS cliente,
                servicios.id AS servicio_id,
                servicios.nombre AS servicio,
                MAX(servicios.precio) AS precio_servicio,
                detalle_afiliado.costo_mensual AS monto_mensual,
                duracion_meses.duracion AS duracion_meses,
                COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pagadas,
                duracion_meses.duracion - COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pendientes,
                CAST((duracion_meses.duracion - COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0)) * detalle_afiliado.costo_mensual AS INT) AS monto_pendiente,
                COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN detalle_afiliado.costo_mensual ELSE 0 END), 0) AS monto_pagado"
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
            ->groupBy('afiliados.id', 'personas.nombres', 'personas.apellidos', 'servicios.id', 'servicios.nombre', 'detalle_afiliado.costo_mensual', 'duracion_meses.duracion')
            ->orderBy('afiliados.id', 'asc')
            ->get();

            if (count($afiliados) > 0 ) {
                $response = [ 'status' => true, 'message' => 'existen datos', 'data' => $afiliados ]; 
            } else {
                $response = [ 'status' => false, 'message' => 'no existen datos', 'data' => null ]; 
            }   
        } else {
            $afiliados = DB::table('afiliados')
            ->selectRaw(
                "afiliados.id AS afiliado_id,
                CONCAT(personas.nombres, ' ', personas.apellidos) AS cliente,
                servicios.id AS servicio_id,
                servicios.nombre AS servicio,
                MAX(servicios.precio) AS precio_servicio,
                detalle_afiliado.costo_mensual AS monto_mensual,
                duracion_meses.duracion AS duracion_meses,
                COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pagadas,
                duracion_meses.duracion - COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pendientes,
                CAST((duracion_meses.duracion - COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0)) * detalle_afiliado.costo_mensual AS INT) AS monto_pendiente,
                COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN detalle_afiliado.costo_mensual ELSE 0 END), 0) AS monto_pagado"
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
            ->where('afiliados.id',$afiliadoIdOrTodos)
            ->groupBy('afiliados.id', 'personas.nombres', 'personas.apellidos', 'servicios.id', 'servicios.nombre', 'detalle_afiliado.costo_mensual', 'duracion_meses.duracion')
            ->orderBy('afiliados.id', 'asc')
            ->get();

            if (count($afiliados) > 0 ) {
                $response = [ 'status' => true, 'message' => 'existen datos', 'data' => $afiliados ]; 
            } else {
                $response = [ 'status' => false, 'message' => 'no existen datos', 'data' => null ]; 
            }
        }

        return response()->json($response);


        // $afiliados = Afiliado::join('clientes as c','c.id','=','cliente_id')
        //                     ->join('personas as p', 'p.id', '=', 'c.persona_id')
        //                     ->join('detalle_afiliado as da', 'afiliados.id', '=', 'da.afiliado_id')
        //                     ->join('duracion_meses as dm', 'da.duracion_mes_id', '=', 'dm.id')
        //                     ->join('servicios as s', 'da.servicio_id', '=', 's.id')
        //                     ->leftJoin('pagos as pg', 'afiliados.id', '=', 'pg.afiliado_id')
        //                     ->leftJoin('detalle_pago as dp', function ($join) {
        //                         $join->on('pg.id', '=', 'dp.pago_id')
        //                             ->on('da.servicio_id', '=', 'dp.servicio_id');
        //                     })
        //                     ->select(
        //                         'afiliados.id AS afiliado_id',
        //                         DB::raw("CONCAT(p.nombres, ' ', p.apellidos) AS cliente"),
        //                         's.id AS servicio_id',
        //                         's.nombre AS servicio',
        //                         DB::raw("MAX(s.precio) AS precio_servicio"),
        //                         'da.costo_mensual AS monto_mensual',
        //                         'dm.duracion AS duracion_meses',
        //                         DB::raw("COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pagadas"),
        //                         DB::raw("dm.duracion - COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pendientes"),
        //                         DB::raw("CAST((dm.duracion - COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN 1 ELSE 0 END), 0)) * da.costo_mensual AS INT) AS monto_pendiente"),
        //                         DB::raw("COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN da.costo_mensual ELSE 0 END), 0) AS monto_pagado")
        //                     )
        //                     ->groupBy('afiliados.id', 'p.nombres', 'p.apellidos', 's.id', 's.nombre', 'da.costo_mensual', 'dm.duracion')
        //                     ->orderBy('afiliados.id', 'asc')
        //                     ->get();


        // $query = DB::table('afiliados as af')
        //     ->join('clientes as c', 'af.cliente_id', '=', 'c.id')
        //     ->join('personas as p', 'p.id', '=', 'c.persona_id')
        //     ->join('detalle_afiliado as da', 'af.id', '=', 'da.afiliado_id')
        //     ->join('duracion_meses as dm', 'da.duracion_mes_id', '=', 'dm.id')
        //     ->join('servicios as s', 'da.servicio_id', '=', 's.id')
        //     ->leftJoin('pagos as pg', 'af.id', '=', 'pg.afiliado_id')
        //     ->leftJoin('detalle_pago as dp', function ($join) {
        //         $join->on('pg.id', '=', 'dp.pago_id')
        //             ->on('da.servicio_id', '=', 'dp.servicio_id');
        //     })
        //     ->select(
        //         'af.id AS afiliado_id',
        //         DB::raw("CONCAT(p.nombres, ' ', p.apellidos) AS cliente"),
        //         's.id AS servicio_id',
        //         's.nombre AS servicio',
        //         DB::raw("MAX(s.precio) AS precio_servicio"),
        //         'da.costo_mensual AS monto_mensual',
        //         'dm.duracion AS duracion_meses',
        //         DB::raw("COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pagadas"),
        //         DB::raw("dm.duracion - COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pendientes"),
        //         DB::raw("CAST((dm.duracion - COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN 1 ELSE 0 END), 0)) * da.costo_mensual AS INT) AS monto_pendiente"),
        //         DB::raw("COALESCE(SUM(CASE WHEN dp.mes IS NOT NULL THEN da.costo_mensual ELSE 0 END), 0) AS monto_pagado")
        //     )
        //     ->groupBy('af.id', 'p.nombres', 'p.apellidos', 's.id', 's.nombre', 'da.costo_mensual', 'dm.duracion')
        //     ->orderBy('af.id', 'asc')
        //     ->get();
    }


    public function obtenerInformacionPorAfilicionId($afiliacion_id)//REPORTE DE TODOS LOS AFILIADOS 
    {
        $afiliados = DB::table('afiliados')
                    ->selectRaw(
                        "afiliados.id AS afiliado_id,
                        CONCAT(personas.nombres, ' ', personas.apellidos) AS cliente,
                        servicios.id AS servicio_id,
                        servicios.nombre AS servicio,
                        MAX(servicios.precio) AS precio_servicio,
                        detalle_afiliado.costo_mensual AS monto_mensual,
                        duracion_meses.duracion AS duracion_meses,
                        COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pagadas,
                        duracion_meses.duracion - COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0) AS letras_pendientes,
                        CAST((duracion_meses.duracion - COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN 1 ELSE 0 END), 0)) * detalle_afiliado.costo_mensual AS INT) AS monto_pendiente,
                        COALESCE(SUM(CASE WHEN detalle_pago.mes IS NOT NULL THEN detalle_afiliado.costo_mensual ELSE 0 END), 0) AS monto_pagado"
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
                    ->where('afiliados.id',$afiliacion_id)
                    ->groupBy('afiliados.id', 'personas.nombres', 'personas.apellidos', 'servicios.id', 'servicios.nombre', 'detalle_afiliado.costo_mensual', 'duracion_meses.duracion')
                    ->orderBy('afiliados.id', 'asc')
                    ->get();

        $response = [
            'status' => true,
            'message' => 'existen datos',
            'data' => $afiliados,   
        ]; 

        return response()->json($response);

    }
}
