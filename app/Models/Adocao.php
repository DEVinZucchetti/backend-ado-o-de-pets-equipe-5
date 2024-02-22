<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adocao extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_id',
        'name',
        'email',
        'cpf',
        'contact',
        'observations',
        'status'
    ];

    public function pet(){
        return $this->HasOne(Pet::class, 'id', 'pet_id');
    }
}
