<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Repartidor,Venta};

class Asignacion_Venta_Repartidor extends Model
{
    use HasFactory;
    protected $table = 'asignacion_venta_repartidor';
    protected $fillable = ['repartidor_id','venta_id'];
    public $timestamps = false;

    public function repartidor(){
        return $this->belongsTo(Repartidor::class);
    }

    public function venta(){
        return $this->belongsTo(Venta::class);
    }
}
