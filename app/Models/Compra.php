<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\{User,Proveedor,Estado,Detalle_Compra,Movimiento};

class Compra extends Model
{
    use HasFactory;
    protected $table = 'compras';
    protected $fillable = ['user_id','proveedor_id','estado_id','serie','descuento','iva','subtotal','total','fecha','estado'];
    public $timestamps = false;

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function proveedor(){
        return $this->belongsTo(Proveedor::class);
    }

    public function estado(){
        return $this->belongsTo(Estado::class);
    }

    public function detalle_compra(){
        return $this->hasMany(Detalle_Compra::class);
    }

    public function movimiento(){
        return $this->hasMany(Movimiento::class);
    }
}
