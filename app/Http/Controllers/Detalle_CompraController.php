<?php

namespace App\Http\Controllers;

use App\Models\Detalle_Compra;
use Illuminate\Http\Request;

class Detalle_CompraController extends Controller
{
    public function guardarDetalleCompra($compra_id, $detalle_compra = []){
        $response = [];
        if(count($detalle_compra) > 0){
            foreach($detalle_compra as $dc){
                $nuevoDetalleCompra = new Detalle_Compra();
                $nuevoDetalleCompra->compra_id = intval($compra_id);
                $nuevoDetalleCompra->producto_id = intval($dc['producto_id']);
                $nuevoDetalleCompra->cantidad = intval($dc['cantidad']);
                $nuevoDetalleCompra->precio = doubleval($dc['precio']);
                $nuevoDetalleCompra->total = doubleval($dc['subTotal']);
                $nuevoDetalleCompra->save();
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
