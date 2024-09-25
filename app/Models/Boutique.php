<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boutique extends Model
{
    use HasFactory;

    protected $fillable = ['nomBoutique', 'description', 'logo', 'adresse', 'telephone', 'user_id',];

    /**
     * Relation avec user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec Produit
     */
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

}
