<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\{Catalogo,Compra};

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';
    protected $fillable = ['ruc','razon_social','direccion','correo','celular','telefono','estado'];
    public $timestamps = false;

    public function catalogo(){
        return $this->hasMany(Catalogo::class);
    }

    public function compra(){
        return $this->hasMany(Compra::class);
    }
}
