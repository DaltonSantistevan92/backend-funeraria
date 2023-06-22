<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Provincia;
use App\Models\Servicios;
use App\Models\Venta;
use App\Models\Venta_Ubicacion;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    private $limiteSerie = 10;
    private $detalleVentaCtrl;
    private $invCtrl;

    public function __construct()
    {
        $this->detalleVentaCtrl = new Detalle_VentaController();
        $this->invCtrl = new InventarioController();
    }

    public function saveVenta(Request $request)
    {
        $ventaRequest = (object) $request->venta;
        $detalleVentaRequest = (array) $request->detalle_venta;
        $ventaUbicacionRequest = (object) $request->venta_ubicacion;

        $serieAutomatica = $this->generate_key($this->limiteSerie);
        $response = [];

        if ($ventaRequest) {

            $existeSerie = Venta::where('serie', $serieAutomatica)->get()->first();

            if ($existeSerie) {
                $response = ['status' => false, 'message' => 'La serie de la venta ya existe', 'data' => null];
            } else {
                $nuevaVenta = new Venta();
                $nuevaVenta->cliente_id = intval($ventaRequest->cliente_id);
                $nuevaVenta->estado_id = 1; //pendiente
                $nuevaVenta->subtotal = doubleval($ventaRequest->subtotal);
                $nuevaVenta->iva = doubleval($ventaRequest->iva);
                $nuevaVenta->total = doubleval($ventaRequest->total);
                $nuevaVenta->serie = $serieAutomatica;
                $nuevaVenta->status = 'A';

                //validar si puede vender productos - caso contrario excede al stock no puede hacerce la venta
                $valid = $this->validarExistencia($detalleVentaRequest);

                //validar que exista la provincia
                $existeProvId = $this->buscarProviciaId($ventaUbicacionRequest->provincia);

                if ($valid == null) {
                    $response = ['status' => false, 'message' => 'La cantidad excede al stock de producto..!'];
                } else if ($existeProvId == null) {
                    $response = ['status' => false, 'message' => 'La provincia es obligatoria'];
                } else {

                    if ($nuevaVenta->save()) {
                        //guardar en venta ubicacion
                        $nuevaVentaUbicacion = new Venta_Ubicacion();
                        $nuevaVentaUbicacion->venta_id = $nuevaVenta->id;
                        $nuevaVentaUbicacion->provincia_id = intval($existeProvId);
                        $nuevaVentaUbicacion->canton = $ventaUbicacionRequest->canton;
                        $nuevaVentaUbicacion->parroquia = $ventaUbicacionRequest->parroquia;
                        $nuevaVentaUbicacion->latitud = doubleval($ventaUbicacionRequest->latitud);
                        $nuevaVentaUbicacion->longitud = doubleval($ventaUbicacionRequest->longitud);
                        $nuevaVentaUbicacion->save();

                        //Guardar detalle de venta
                        $this->detalleVentaCtrl->guardarDetalleVenta($nuevaVenta->id, $detalleVentaRequest);

                        $response = ['status' => true, 'message' => 'Su pedido se registro correctamente'];

                    } else {
                        $response = ['status' => false, 'message' => 'No se puede guardar el pedido'];
                    }
                }
            }
        } else {
            $response = ['status' => false, 'message' => 'No hay datos para procesar'];
        }
        return response()->json($response);
    }

    private function validarExistencia($array)
    {
        if (count($array) > 0) {
            foreach ($array as $arr) {
                $producto_id = intval($arr['producto_id']);
                $cantidad = intval($arr['cantidad']);

                if ($producto_id === null || intval($arr['servicio_id'])) {
                    return true;
                }

                $existe = $this->validarExistenciaProducto($producto_id, $cantidad);

                if ($existe) {
                    return $existe;
                } else {
                    return null;
                }
            }
        }
    }

    private function validarExistenciaProducto($producto_id, $cantidad)
    {
        $dataProducto = Producto::find($producto_id);

        if ($dataProducto) {
            if ((intval($cantidad) > intval($dataProducto->stock))) {
                return false; // Devuelve falso si la cantidad supera el stock del producto
            } else {
                return true; // Devuelve verdadero si la cantidad está dentro del stock del producto
            }
        }

    }

    private function buscarProviciaId($nombre_provincia)
    {
        $provincia = Provincia::where('provincia', $nombre_provincia)->first();

        return $provincia ? $provincia->id : null;
    }

    public function generate_key($limit)
    {
        $key = '';

        $aux = sha1(md5(time()));
        $key = substr($aux, 0, $limit);

        return $key;
    }

    public function tableVentas($estado_id)
    {
        $venta = Venta::where('estado_id', intval($estado_id))->get();
        $response = [];

        if ($venta->count() > 0) {
            foreach ($venta as $v) {
                $v->cliente->persona;
                $v->estado;

                foreach ($v->detalle_venta as $dv) {
                    $dv->producto;
                    $dv->servicio;
                }
            }
            $response = [
                'status' => true,
                'message' => 'existen datos',
                'data' => $venta,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'no existen datos',
                'data' => null,
            ];
        }
        return response()->json($response);
    }

    public function setEstadoVenta($venta_id, $estado_id, $user_id)
    {
        $anulado = 3;  $enProceso = 6;  $entregado = 2;  $mensaje = '';
        $estado_id = intval($estado_id);  $venta_id = intval($venta_id);
        $response = [];

        switch ($estado_id) {
            case 2:$mensaje = 'El pedido ah sido entregado'; break;//ojo
            case 3:$mensaje = 'El pedido ah sido anulado'; break;
            case 6:$mensaje = 'El pedido esta en proceso'; break;
        }

        if ($estado_id === $anulado || $estado_id === $entregado || $estado_id === $enProceso) {
            $venta = Venta::find($venta_id);

            if ($venta) {
                $venta->user_id = $user_id;
                $venta->estado_id = $estado_id;

                if ($estado_id === $enProceso) {
                    $detallesVenta = $venta->detalle_venta;

                    if ($detallesVenta->count() > 0) {
                        foreach ($detallesVenta as $dv) {
                            if ($dv->producto_id !== null) {
                                if ($this->isProductoListo($dv->producto_id)) {
                                    $aux = [
                                        'producto_id' => $dv->producto_id,
                                        'cantidad' => $dv->cantidad
                                    ];
                                    $productosListos[] = (object) $aux;
                                }
                            } else if ($dv->servicio_id !== null) {
                                if ($this->isServicioListo($dv->servicio_id)) {
                                    $serviciosListos[] = $dv->servicio_id;
                                }
                            }
                        }

                        if (!empty($productosListos)) {
                            $response = $this->verificarProductos($productosListos);

                            if ($response['status'] === false) {
                                return $response;
                            } else {  
                                if (count($response['productos_por_actualizar']) > 0) {
                                    $venta->save();

                                    $this->actualizarProducto($response['productos_por_actualizar']);

                                    //$response = ['status' => true, 'message' => $response['message']];
                                    //return $response; 
                                    //add movimiento
                                    $nuevoMovimiento = $this->nuevoMovimiento($venta->id);
                                    //add inventario
                                    $this->invCtrl->guardarIngresoProductos($nuevoMovimiento->id, $detallesVenta, $nuevoMovimiento->tipo);
                                    return $response = ['status' => true, 'message' => $mensaje];
                                }

                            }
                        }
    
                        if (!empty($serviciosListos)) {
                            $response = ['status' => true, 'message' => 'Su servicio está listo'];
                            return $response;
                        }
                    }

                }
                $venta->save();
                $response = ['status' => true, 'message' => $mensaje];
            } else {
                $response = ['status' => false, 'message' => 'No hay datos para procesar'];
            }
        } else {
            $response = ['status' => false, 'message' => 'El estado no existe'];
        }

        return response()->json($response);
    }

    protected function isProductoListo($producto_id)
    {
        $producto = Producto::find($producto_id);
        if ($producto) { return true; }
        return false;
    }

    protected function isServicioListo($servicio_id)
    {
        $servicio = Servicios::find($servicio_id);
        if ($servicio) { return true; }
        return false;
    }

    protected function verificarProductos($productosListos)
    {
        foreach ($productosListos as $producto) {
            $producto_id = intval($producto->producto_id);
            $cantidad = intval($producto->cantidad);

            $producto = Producto::find($producto_id);

            if ($producto) {
                if (intval($producto->stock) >= $cantidad) {

                    $productosPorActualizar[] = [
                        'producto_id' => $producto_id,
                        'nombre' => $producto->nombre,
                        'stock_disponible' => $producto->stock,
                        'cantidad_requerida' => $cantidad
                    ];   
                } else {
                    // Manejo de error: el stock es insuficiente
                    $productosInsuficientes[] = [
                        'producto_id' => $producto_id,
                        'nombre' => $producto->nombre,
                        'stock_disponible' => $producto->stock,
                        'cantidad_requerida' => $cantidad
                    ];
                }
            }
        }

        if (!empty($productosInsuficientes)) {
            $response = [
                'status' => false,
                'message' => 'El stock es insuficiente para algunos productos',
                'productos_insuficientes' => $productosInsuficientes
            ];
            return $response;
        }
    
        $response = [
            'status' => true,
            'message' => 'Los productos se han actualizado correctamente',
            'productos_por_actualizar' => $productosPorActualizar

        ];
        return $response;
    }

    protected function actualizarProducto($productos_por_actualizar)
    {
        foreach ($productos_por_actualizar as $p) {
            $producto_id = intval($p['producto_id']);
            $cantidad = intval($p['cantidad_requerida']);

            $producto = Producto::find($producto_id);
            $producto->stock -= $cantidad;
            $producto->save();
        }
    }

    protected function nuevoMovimiento($venta_id){
        $newMovimiento = new Movimiento();
        $newMovimiento->venta_id = intval($venta_id);
        $newMovimiento->tipo = 'S';
        $newMovimiento->fecha = date('Y-m-d');
        $newMovimiento->save();

        return $newMovimiento;
    }
}
