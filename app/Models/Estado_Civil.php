<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Afiliado;


class Estado_Civil extends Model
{
    use HasFactory;
    protected $table = 'estado_civil';
    protected $fillable = ['status','estado'];
    public $timestamps = false;


    public function afiliado(){
        return $this->hasMany(Afiliado::class);
    }
}
