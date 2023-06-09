<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\{Afiliado,Compra,Venta};

class Estado extends Model
{
    use HasFactory;

    protected $table = 'estados';
    protected $fillable = ['detalle','estado'];
    public $timestamps = false;


    public function afiliado(){
        return $this->hasMany(Afiliado::class);
    }

    public function compra(){
        return $this->hasMany(Compra::class);
    }

    public function venta(){
        return $this->hasMany(Venta::class,'estado_id');
    }

    
}
