<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\{Movimiento,Producto};

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';
    protected $fillable = ['movimiento_id','producto_id','cantidad','precio','total','cantidad_disponible','precio_disponible','total_disponible','fecha'];

    public function movimiento(){
        return $this->belongsTo(Movimiento::class);
    }

    public function producto(){
        return $this->belongsTo(Producto::class);
    }
}
