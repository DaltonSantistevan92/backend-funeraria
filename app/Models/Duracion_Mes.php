<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Duracion_Mes extends Model
{
    use HasFactory;
    protected $table = 'duracion_meses';
    protected $fillable = ['duracion','estado'];
    public $timestamps = false;

    public function detalle_afiliado(){
        return $this->hasMany(Detalle_Afiliado::class);
    }
}
