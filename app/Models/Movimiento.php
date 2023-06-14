<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Venta,Compra,Inventario};

class Movimiento extends Model
{
    use HasFactory;
    protected $table = 'movimientos';
    protected $fillable = ['venta_id','compra_id','tipo','fecha'];
    
    public function venta(){
        return $this->belongsTo(Venta::class);
    }

    public function compra(){
        return $this->belongsTo(Compra::class);
    }

    public function inventario(){
        return $this->hasMany(Inventario::class);
    }

}
