<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Afiliado,Parentesco};

class Contacto_Emergencia extends Model
{
    use HasFactory;
    protected $table = 'contacto_emergencia';
    protected $fillable = ['afiliado_id','parentesco_id','nombre','num_celular'];
    public $timestamps = false;

    public function afiliado(){
        return $this->belongsTo(Afiliado::class);
    }

    public function parentesco(){
        return $this->belongsTo(Parentesco::class);
    }
}
