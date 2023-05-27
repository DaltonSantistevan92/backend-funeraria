<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permiso;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'menus';
    protected $fillable = ['id_seccion','menu','url','icono','posicion','estado'];
    public $timestamps = false;

    public function permiso(){
        return $this->hasMany(Permiso::class);
    }
}
