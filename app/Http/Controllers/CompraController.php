<?php

namespace App\Http\Controllers;

use App\Models\{Compra,Producto,Configuracion, Movimiento, Venta};
use Illuminate\Http\Request;

class CompraController extends Controller
{
    private $limiteSerie = 10;
    private $detalleCompraCtrl;
    private $invCtrl;

    public function __construct()
    {
        $this->detalleCompraCtrl = new Detalle_CompraController();
        $this->invCtrl = new InventarioController();
    }
    
    public function tableCompras($estado_id){
        $compra = Compra::where('estado_id',intval($estado_id))->get();
        $response = [];

        if ($compra->count() > 0) {
            foreach($compra as $c){
                $c->user->persona;
                $c->proveedor;
                $c->estado;

                foreach($c->detalle_compra as $dc){
                    $dc->producto;
                }
            }
            $response = [
                'status'=> true,
                'message' => 'existen datos',
                'data' => $compra,
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

    public function guardarCompra(Request $request){
        $compraRequest = (object) $request->compra;
        $detalleCompraRequest = (array) $request->detalle_compra;
        $serieAutomatica = $this->generate_key($this->limiteSerie);
        $response = [];
 
        if ($compraRequest) {
            $nuevaCompra = new Compra();
            $nuevaCompra->user_id = intval($compraRequest->user_id);
            $nuevaCompra->proveedor_id = intval($compraRequest->proveedor_id);
            $nuevaCompra->estado_id = 1;
            $nuevaCompra->serie = $serieAutomatica;
            $nuevaCompra->total = doubleval($compraRequest->total);
            //$nuevaCompra->fecha = date('Y-m-d');
            $nuevaCompra->status = 'A';

            $existeSerie = Compra::where('serie', $serieAutomatica)->get()->first();
            
            if ($existeSerie) {
                $response = [ 'status' => false, 'message' => 'La serie de la compra ya existe', 'data' => null ];
            } else {
                if ($nuevaCompra->save()) { 
                    //Guardar detalle de compras
                    $this->detalleCompraCtrl->guardarDetalleCompra($nuevaCompra->id, $detalleCompraRequest);
                    
                    $response = [ 'status' => true,'message' => 'La compra se ha guardado correctamente' ];
                } else {
                    $response = [ 'status' => false, 'message' => 'No se puede guardar la compra' ];
                }
            }
        } else {
            $response = [ 'status' => false, 'message' => 'No hay datos para procesar' ];
        }
        return response()->json($response);
    }

    public function generate_key($limit){
        $key = '';

        $aux = sha1(md5(time()));
        $key = substr($aux, 0, $limit);

        return $key;
    }

    /* public function setEstadoCompra2($compra_id,$estado_id){
        $anulado = 3;  $recibido = 5; $mensaje = '';
        $estado_id = intval($estado_id);

        switch ($estado_id) {
            case 3:
                $mensaje = 'La compra ah sido anulada';
            break;
            case 5:
                $mensaje = 'La compra ah sido recibida';
            break;  
        }

        if ($estado_id === $anulado) {
            $compras = Compra::find(intval($compra_id));

            if ($compras) {
                $compras->estado_id = $estado_id;
                $compras->fecha = date('Y-m-d');
                $compras->save();

                $response = [ 'status' => true, 'message' => $mensaje ];
            } else {
                $response = [ 'status' => false, 'message' => 'No hay datos para procesar' ];
            }    
        }else if($estado_id === $recibido){
            $compras = Compra::find(intval($compra_id));

            if ($compras) {
                $compras->estado_id = $estado_id;
                $compras->fecha = date('Y-m-d');
                $compras->save();

                //actualizar el stock, precio de venta y margen de ganancia de productos
                $detallesCompras = $compras->detalle_compra;

                if ($detallesCompras->count() > 0) { 
                    foreach($detallesCompras as $dc){
                        $producto_id = $dc->producto_id;
                        $cantidad = $dc->cantidad;
                        $precio = $dc->precio;
                        $this->actualizarStockPrecioVentaMargendeGanancia($producto_id, $cantidad, $precio );
                    }  
                } 

                //se inserta un nuevo movimiento
                $nuevoMovimiento = $this->nuevoMovimiento($compras->id); 

                //se actualiza el inventario
                $this->invCtrl->guardarIngresoProductos($nuevoMovimiento->id, $detallesCompras, $nuevoMovimiento->tipo);
        
                $response = [ 'status' => true, 'message' => $mensaje ];
            } else {
                $response = [ 'status' => false, 'message' => 'No hay datos para procesar' ];
            }    

        }else {
            // no existe el estado
        }

        return response()->json($response);
    } */

    public function setEstadoCompra($compra_id,$estado_id){
        $anulado = 3;  $recibido = 5; $mensaje = '';
        $estado_id = intval($estado_id);

        switch ($estado_id) {
            case 3 : $mensaje = 'La compra ah sido anulada'; break;
            case 5: $mensaje = 'La compra ah sido recibida'; break;  
        }

        if ($estado_id === $anulado || $estado_id === $recibido) { 
            $compra = Compra::find(intval($compra_id));

            if ($compra) {
                $compra->estado_id = $estado_id;
                $compra->fecha = date('Y-m-d');
                $compra->save();

                if ($estado_id === $recibido) { 
                    $detallesCompra = $compra->detalle_compra;

                    if ($detallesCompra->count() > 0) {
                        foreach ($detallesCompra as $detalleCompra) {
                            $producto_id = $detalleCompra->producto_id;
                            $cantidad = $detalleCompra->cantidad;
                            $precio = $detalleCompra->precio;
                            $this->actualizarStockPrecioVentaMargenDeGanancia($producto_id, $cantidad, $precio);
                        }
                    }

                    $nuevoMovimiento = $this->nuevoMovimiento($compra->id);
                    $this->invCtrl->guardarIngresoProductos($nuevoMovimiento->id, $detallesCompra, $nuevoMovimiento->tipo);
                } 
                $response = ['status' => true, 'message' => $mensaje];
            } else {
                $response = ['status' => false, 'message' => 'No hay datos para procesar'];
            }
        } else {
            $response = ['status' => false, 'message' => 'El estado no existe'];
        }
        return response()->json($response);
    }

    protected function actualizarStockPrecioVentaMargendeGanancia($producto_id, $cantidad, $precio_compra ){
        $configuraciones = Configuracion::all();
        $porcentaje_ganancia = $configuraciones[0]->porcentaje_ganancia;
        
        //formula del precio de venta
        //pv = pc + ( ( pc * porcentajeGanancia) / 100) )
        $precio_ventaf = 0.00;  $margenGananciaf = 0.00;
        $precio_ventaf = round(doubleval($precio_compra) + ( ( doubleval($precio_compra) * intval($porcentaje_ganancia) / 100 ) ), 2);

        $margenGananciaf = round($precio_ventaf - doubleval($precio_compra),2);

        $producto = Producto::find($producto_id);
        $producto->stock += $cantidad;
        $producto->precio_venta = $precio_ventaf;
        $producto->margen_ganancia = $margenGananciaf;
        $producto->save();
    }

    protected function nuevoMovimiento($compra_id){
        $newMovimiento = new Movimiento();
        $newMovimiento->compra_id = intval($compra_id);
        $newMovimiento->tipo = 'E';
        $newMovimiento->fecha = date('Y-m-d');
        $newMovimiento->save();

        return $newMovimiento;
    }

    public function dashCompraAndVenta(){
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $year = date('Y');  $entregado = 2;  $recibido = 5;  $response = [];   $dataCompra = [];  $dataVenta = [];

        for ($i = 0; $i < count($meses); $i++) {
            $compras = Compra::where('status','A')->where('estado_id',$recibido)->whereYear('fecha', '=', $year)->whereMonth('fecha','=',$i+1)->get()->sum('total');

            $ventas = Venta::where('status','A')->where('estado_id',$entregado)->whereYear('fecha_hora_entrega', '=', $year)->whereMonth('fecha_hora_entrega','=',$i+1)->get()->sum('total');

            $dataCompra[] = ($compras > 0) ? round($compras,2) : 0;
            $dataVenta[] = ($ventas > 0) ? round($ventas,2) : 0;
            
            $response = [
                'compra' => [ 'labels' => $meses, 'data' => $dataCompra, 'anio' => $year ],
                'venta' => [ 'labels' => $meses, 'data' => $dataVenta, 'anio' => $year ]
            ];
        }
        return response()->json($response);
    }

    public function totalCompraAndVenta(){
        $entregado = 2;  $recibido = 5;  $response = [];
        $compras = Compra::where('status','A')->where('estado_id',$recibido)->get()->sum('total');
        $ventas = Venta::where('status','A')->where('estado_id',$entregado)->get()->sum('total');

        if ($compras || $ventas) {
            $response = [
                'status' => true,
                'message' => 'existe datos',
                'data' => [
                    'compra' => ($compras > 0) ? round($compras,2) : 0,
                    'venta' => ($ventas > 0) ? round($ventas,2) : 0
                ],
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
