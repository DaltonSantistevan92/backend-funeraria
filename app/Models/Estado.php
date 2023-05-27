<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Afiliado;

class Estado extends Model
{
    use HasFactory;

    protected $table = 'estados';
    protected $fillable = ['detalle','estado'];
    public $timestamps = false;


    public function afiliado(){
        return $this->hasMany(Afiliado::class);
    }
}
