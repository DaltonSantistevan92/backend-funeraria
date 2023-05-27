<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Menu,Rol,Tipo};


class Permiso extends Model
{
    use HasFactory;
    protected $table = 'permisos';
    protected $fillable = ['menu_id','rol_id','tipo_id','acceso','estado'];
    public $timestamps = false;

    public function menu(){
        return $this->belongsTo(Menu::class);
    }

    public function rol(){
        return $this->belongsTo(Rol::class);
    }

    public function tipo(){
        return $this->belongsTo(Tipo::class);
    }


    
}
