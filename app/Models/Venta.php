<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Movimiento;

class Venta extends Model
{
    use HasFactory;



    public function movimiento(){
        return $this->hasMany(Movimiento::class);
    }
}
