<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adresse extends Model
{
    use HasFactory;

    protected $fillable = [
        'pays',
        'ville',
        'codePostal',

    ];

    public function users()
    {
        return $this->hasMany(User::class, 'adresse_id');
    }
}
