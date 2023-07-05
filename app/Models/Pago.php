<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{Afiliado};

class Pago extends Model
{
    use HasFactory;
    protected $table = 'pagos';
    protected $fillable = ['afiliado_id','monto'];
    public $timestamps = false;

    public function afiliado(){
        return $this->belongsTo(Afiliado::class);
    }

    public function detalle_pago(){
        return $this->hasMany(Detalle_Pago::class); 
    }
}
