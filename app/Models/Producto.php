<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'productos';
    protected $fillable = ['categoria_id','codigo','nombre','descripcion','imagen','stock','precio_compra','precio_venta','margen_ganacia','fecha','estado'];
    public $timestamps = false;

    public function categoria(){
        return $this->belongsTo(Categoria::class);
    }
}
