<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\{Movimiento,Venta_Ubicacion,User,Cliente,Estado,Detalle_Venta,Asignacion_Venta_Repartidor};

class Venta extends Model
{
    use HasFactory;
    protected $table = 'ventas';
    protected $fillable = ['user_id','cliente_id','estado_id','descuento','subtotal','iva','total','serie','fecha_hora_entrega','status','asignado'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function estado(){
        return $this->belongsTo(Estado::class);
    }

    public function movimiento(){
        return $this->hasMany(Movimiento::class);
    }

    public function venta_ubicacion(){
        return $this->hasMany(Venta_Ubicacion::class);
    }

    public function detalle_venta(){
        return $this->hasMany(Detalle_Venta::class);
    }

    public function asignacion_venta_repartidor(){
        return $this->hasMany(Asignacion_Venta_Repartidor::class);
    }
}
