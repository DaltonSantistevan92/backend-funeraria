<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Venta,Producto,Servicios};

class Detalle_Venta extends Model
{
    use HasFactory;
    protected $table = 'detalle_venta';
    protected $fillable = ['venta_id','producto_id','servicio_id','cantidad','precio','total','estado'];
    public $timestamps = false;

    public function venta(){
        return $this->belongsTo(Venta::class);
    }

    public function producto(){
        return $this->belongsTo(Producto::class);
    }

    public function servicio(){
        return $this->belongsTo(Servicios::class);
    }

}
