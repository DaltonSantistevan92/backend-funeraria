<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Cliente};


class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';
    protected $fillable = ['cedula','nombres','apellidos','celular','direccion','estado'];
    public $timestamps = false;

    public function cliente(){
        return $this->hasMany(Cliente::class);
    }
    
}
