<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Afiliado,Servicios,Duracion_Mes};

class Detalle_Afiliado extends Model
{
    use HasFactory;
    protected $table = 'detalle_afiliado';
    protected $fillable = ['afiliado_id','servicio_id','duracion_mes_id','costo_mensual'];
    public $timestamps = false;

    public function afiliado(){
        return $this->belongsTo(Afiliado::class);
    }

    public function servicio(){
        return $this->belongsTo(Servicios::class);
    }

    public function duracion_mes(){
        return $this->belongsTo(Duracion_Mes::class);
    }
}
