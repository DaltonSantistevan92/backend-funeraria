<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Pago,Servicios};

class Detalle_Pago extends Model
{
    use HasFactory;
    protected $table = 'detalle_pago';
    protected $fillable = ['pago_id','servicio_id','mes','fecha_pago','total_pagado'];
    public $timestamps = false;

    public function pago(){
        return $this->belongsTo(Pago::class);
    }

    public function servicio(){
        return $this->belongsTo(Servicios::class);
    }
}
