<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Persona,Afiliado};


class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes';
    protected $fillable = ['persona_id','estado'];
    public $timestamps = false;

    public function persona(){
        return $this->belongsTo(Persona::class);
    }

    public function afiliado(){
        return $this->hasMany(Afiliado::class);
    }
}
