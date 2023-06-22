<?php

namespace App\Http\Controllers;

use App\Models\Detalle_Venta;
use Illuminate\Http\Request;

class Detalle_VentaController extends Controller
{
    public function guardarDetalleVenta($venta_id, $detalle_venta = []){
        $response = [];
        if(count($detalle_venta) > 0){
            foreach($detalle_venta as $dv){
                $nuevoDetalleVenta = new Detalle_Venta();
                $nuevoDetalleVenta->venta_id = intval($venta_id);
                $nuevoDetalleVenta->producto_id = $dv['producto_id'];
                $nuevoDetalleVenta->servicio_id = $dv['servicio_id'];
                $nuevoDetalleVenta->cantidad = intval($dv['cantidad']);
                $nuevoDetalleVenta->precio = doubleval($dv['precio']);
                $nuevoDetalleVenta->total = doubleval($dv['total']);
                $nuevoDetalleVenta->save();
            }
            
            $response = [
                'status' => true,
                'message' => 'Se guardo el detalle de productos',
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'No hay productos para guardar',
            ];
        }
        return $response; 
    }
}
