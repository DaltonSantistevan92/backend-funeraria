<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use App\Models\Servicios;

class Categoria extends Model
{
    use HasFactory;
    protected $table = 'categorias';
    protected $fillable = ['nombre_categoria','img','estado'];
    public $timestamps = false;

    public function producto(){
        return $this->hasMany(Producto::class);
    }

    public function servicios(){
        return $this->hasMany(Servicios::class);
    }
}
