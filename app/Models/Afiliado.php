<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\{Cliente,Estado_Civil,Contacto_Emergencia,Estado,Detalle_Afiliado,Pago,Fecha_Pagos};

class Afiliado extends Model
{
    use HasFactory;
    protected $table = 'afiliados';
    protected $fillable = ['cliente_id','estado_civil_id','fecha','estado_id','facturado'];


    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function estado_civil(){
        return $this->belongsTo(Estado_Civil::class);
    }

    public function estado(){
        return $this->belongsTo(Estado::class);
    }

    public function contacto_emergencia(){
        return $this->hasMany(Contacto_Emergencia::class);
    }

    public function detalle_afiliado(){
        return $this->hasMany(Detalle_Afiliado::class);
    }

    public function pago(){
        return $this->hasMany(Pago::class);
    }

    public function fecha_pago(){
        return $this->hasMany(Fecha_Pagos::class);
    }


    
}
