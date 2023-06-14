<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function guardarIngresoProductos($movimiento_id, $detalleCompras = [], $tipo){
        $response = [];  $extra = [];

        if (count($detalleCompras) > 0 ) {
            foreach ($detalleCompras as $item) {
                $nuevoInventario = new Inventario();

                $movimiento_id = intval($movimiento_id);
                $producto_id = intval($item['producto_id']);
                $cantidad = ($tipo == 'E') ? intval($item['cantidad']) : ((-1) * intval($item['cantidad']));
                $precio = ($tipo == 'E') ? doubleval($item['precio']) : doubleval($item['precio']);
                $total = ($tipo == 'E') ? doubleval($item['total']) : doubleval($item['total']);

                $nuevoInventario->movimiento_id = $movimiento_id;
                $nuevoInventario->producto_id = $producto_id;
                $nuevoInventario->cantidad = $cantidad;
                $nuevoInventario->precio = $precio;
                $nuevoInventario->total = $total;
                $nuevoInventario->fecha = date('Y-m-d');

                //1.- Verificar si existe un registro anterior del producto
                $existe = Inventario::where('producto_id', $producto_id)->get()->count();

                if ($existe == 0) { //Primer registro
                    $nuevoInventario->cantidad_disponible = $cantidad;
                    $nuevoInventario->precio_disponible = $precio;
                    $nuevoInventario->total_disponible = $total;
                    
                    $extra = $this->primerRegistroTipo($tipo, $nuevoInventario);
                } else { //Segundo  o más registros
                    $extra = $this->masRegistroTipo($tipo, $nuevoInventario);
                }
            }
            $response = [
                'status' => true,
                'message' => 'Inventario actualizado correctamente',
                'extra' => $extra,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'No se ha actualizado el inventario',
            ];
        }
        return $response;

    }

    private function primerRegistroTipo($tipo, Inventario $inventario){
        $response = [];
        if ($tipo == 'E') {
            $inventario->save();

            $response = [
                'status' => true,
                'message' => 'Primer movimiento del producto ' . $inventario->producto_id,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'No hay productos en stock disponible, realice compras',
            ];
        }
        return $response;
    }

    private function masRegistroTipo($tipo, Inventario $inventario){
        $response = [];
        $ultimoProducto = Inventario::where('producto_id', $inventario->producto_id)->orderBy('id', 'DESC')->get()->first();

        $cantidad = ( intval($inventario->cantidad) + intval($ultimoProducto->cantidad_disponible) );
        $inventario->cantidad_disponible = $cantidad;

        if ($tipo == 'E') {
            $total = round(doubleval($ultimoProducto->total_disponible) + doubleval($inventario->total),2);
            $inventario->total_disponible = $total;

            $inventario->precio_disponible = round(($inventario->total_disponible / $cantidad),2);
        } else {
            if ($tipo == 'S') {
                $precio = doubleval($inventario->precio);
                $inventario->precio = $precio;

                $totalGlobal = round(( intval($ultimoProducto->cantidad_disponible) * doubleval($inventario->precio_disponible)),2);
                $inventario->total_disponible = round($totalGlobal - doubleval($inventario->total),2);
            }
        }

        if ($inventario->save()) {
            $response = [
                'status' => true,
                'message' => 'Inventario actualizado ' . $inventario->producto_id,
                'data' => $inventario,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'No se pudo actualizar el inventario',
                'data' => $inventario,
            ];
        }
        return $response;
    }
}
