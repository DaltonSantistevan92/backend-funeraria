<?php

namespace App\Http\Controllers;

use App\Models\Inventario;

class InventarioController extends Controller
{
    public function guardarIngresoProductos($movimiento_id, $detalleCompras = [], $tipo)
    {
        $response = [];
        $extra = [];

        if (count($detalleCompras) > 0) {
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

    private function primerRegistroTipo($tipo, Inventario $inventario)
    {
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

    private function masRegistroTipo($tipo, Inventario $inventario)
    {
        $response = [];
        $ultimoProducto = Inventario::where('producto_id', $inventario->producto_id)->orderBy('id', 'DESC')->get()->first();

        $cantidad = (intval($inventario->cantidad) + intval($ultimoProducto->cantidad_disponible));
        $inventario->cantidad_disponible = $cantidad;

        if ($tipo == 'E') { //entrada
            $total = round(doubleval($ultimoProducto->total_disponible) + doubleval($inventario->total), 2);
            $inventario->total_disponible = $total;

            $inventario->precio_disponible = round(($inventario->total_disponible / $cantidad), 2);
        } else { //salida
            if ($tipo == 'S') {
                $precio = $inventario->precio;
                $inventario->precio_disponible = $precio;

                $totalGlobal = round(($ultimoProducto->cantidad_disponible * $inventario->precio_disponible), 2);
                $inventario->total_disponible = round(($totalGlobal - $inventario->total), 2);
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

    public function kardex($producto_id, $fecha_inicio, $fecha_fin)
    {
        $inventario = Inventario::where('producto_id', $producto_id)->whereBetween('fecha', [$fecha_inicio, $fecha_fin])->get();

        if (count($inventario) > 0 ) {
            $data = [];  $i = 1;

            foreach ($inventario as $in) {
                $in->producto;
                $in->movimiento->tipo; 
                $entrada = [];  $salida = [];  $tipo = [];
    
                if ($in->movimiento->tipo == 'E') {
                    $entrada = [ 0 => $in->cantidad, 1 => $in->precio, 2 => $in->total ];
                    $salida = [ 0 => '', 1 => '', 2 => ''];
                    $type = ($in->movimiento->tipo == 'E') ? 'Compra - Entrada' : '';
                    $tipo = [ 0 => $type ];
                } else {
                    $salida = [ 0 => abs($in->cantidad), 1 => $in->precio, 2 => $in->total ];
                    $entrada = [ 0 => '', 1 => '', 2 => '' ];
                    $type = ($in->movimiento->tipo == 'S') ? 'Pedido - Salida' : '';
                    $tipo = [ 0 => $type ];
                }
    
                $aux = [
                    'numero' => $i,
                    'fecha' => $in->fecha,
                    'tipo' =>  $tipo[0],
                    'entrada_cantidad' => $entrada[0],
                    'entrada_precio' => $entrada[1],
                    'entrada_total' => $entrada[2],
                    'salida_cantidad' => $salida[0],
                    'salida_precio' => $salida[1],
                    'salida_total' => $salida[2],
                    'disponible_cantidad' => $in->cantidad_disponible,
                    'disponible_precio' => $in->precio_disponible,
                    'disponible_total' => $in->total_disponible
                ];
                $data[] = (object)$aux;
                $i++;
            }

            $response = ['status' => true, 'message' => 'existen datos','data' => $data];
        } else {
            $response = ['status' => false, 'message' => 'no hay datos para procesar', 'data' => null];
        }
        return response()->json($response);
    }
}
