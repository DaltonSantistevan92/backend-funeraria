<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Afiliado;

class Fecha_Pagos extends Model
{
    use HasFactory;
    protected $table = 'fecha_pagos';
    protected $fillable = ['afiliado_id','servicio_id','fecha_pago','isPagado'];
    public $timestamps = false;

    public function afiliado(){
        return $this->belongsTo(Afiliado::class);
    }
}
