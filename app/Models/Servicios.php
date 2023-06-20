<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Categoria,Detalle_Venta};

class Servicios extends Model
{
    use HasFactory;
    protected $table = 'servicios';
    protected $fillable = ['categoria_id','nombre','descripcion','precio','imagen','estado'];
    public $timestamps = false;

    public function categoria(){
        return $this->belongsTo(Categoria::class);
    }

    public function detalle_venta(){
        return $this->hasMany(Detalle_Venta::class);
    }
}
