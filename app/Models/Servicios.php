<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;

class Servicios extends Model
{
    use HasFactory;
    protected $table = 'servicios';
    protected $fillable = ['categoria_id','nombre','descripcion','precio','imagen','estado'];
    public $timestamps = false;

    public function categoria(){
        return $this->belongsTo(Categoria::class);
    }
}
