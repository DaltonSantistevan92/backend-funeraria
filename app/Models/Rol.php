<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Rol extends Model
{
    use HasFactory;
    protected $table = 'roles';
    protected $fillable = ['cargo','estado'];
    public $timestamps = false;

    public function user(){
        return $this->hasMany(User::class);
    }
}
