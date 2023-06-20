<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Venta,Provincia};

class Venta_Ubicacion extends Model
{
    use HasFactory;
    protected $table = 'venta_ubicacion';
    protected $fillable = ['venta_id','provincia_id','canton_id','parroquia_id','latitud','longitud'];
    public $timestamps = false;

    public function venta(){
        return $this->belongsTo(Venta::class);
    }

    public function provincia(){
        return $this->belongsTo(Provincia::class);
    }
}
