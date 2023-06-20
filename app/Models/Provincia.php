<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Venta_Ubicacion};

class Provincia extends Model
{
    use HasFactory;
    protected $table = 'provincias';
    protected $fillable = ['provincia','estado'];
    public $timestamps = false;


    public function venta_ubicacion(){
        return $this->hasMany(Venta_Ubicacion::class);
    }
}
