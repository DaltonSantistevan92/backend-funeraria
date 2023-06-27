<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Persona,Asignacion_Venta_Repartidor};

class Repartidor extends Model
{
    use HasFactory;
    protected $table = 'repartidor';
    protected $fillable = ['persona_id','disponible','estado'];
    public $timestamps = false;

    public function persona(){
        return $this->belongsTo(Persona::class);
    }

    public function asignacion_venta_repartidor(){
        return $this->hasMany(Asignacion_Venta_Repartidor::class);
    }
}
