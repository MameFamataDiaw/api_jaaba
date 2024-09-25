<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse'
    ];

    /**
     * Relation avec le modele User
     */
//    public function user()
//    {
//        return $this->belongsTo(User::class);
//    }

    /**
     * Relation avec le modele Commande
     */
    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}
