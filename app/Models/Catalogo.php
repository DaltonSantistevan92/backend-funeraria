<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\{Proveedor,Producto};

class Catalogo extends Model
{
    use HasFactory;
    protected $table = 'catalogo';
    protected $fillable = ['proveedor_id','producto_id','estado'];
    public $timestamps = false;

    public function proveedor(){
        return $this->belongsTo(Proveedor::class);
    }

    public function producto(){
        return $this->belongsTo(Producto::class);
    }
}
