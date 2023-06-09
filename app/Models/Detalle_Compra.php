<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Compra,Producto};

class Detalle_Compra extends Model
{
    use HasFactory;
    protected $table = 'detalle_compra';
    protected $fillable = ['compra_id','producto_id','cantidad','precio','total'];
    public $timestamps = false;


    public function compra(){
        return $this->belongsTo(Compra::class);
    }

    public function producto(){
        return $this->belongsTo(Producto::class);
    }

}
