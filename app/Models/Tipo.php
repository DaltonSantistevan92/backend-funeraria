<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permiso;

class Tipo extends Model
{
    use HasFactory;
    protected $table = 'tipos';
    protected $fillable = ['type','estado'];
    public $timestamps = false;


    public function permiso(){
        return $this->hasMany(Permiso::class);
    }
}
