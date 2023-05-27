<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contacto_Emergencia;

class Parentesco extends Model
{
    use HasFactory;
    protected $table = 'parentesco';
    protected $fillable = ['relacion','estado'];
    public $timestamps = false;

    public function contacto_emergencia(){
        return $this->hasMany(Contacto_Emergencia::class);
    }
}
