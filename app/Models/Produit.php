<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;
    protected $fillable = ['libelle', 'description', 'prix', 'quantite','photo','categorie_id', 'user_id'];

    /**
     * Relation avec Categorie
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    /**
     * Relation avec Boutique
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commandes(){
        return $this->belongsToMany(Commande::class, 'details_commandes')->withPivot('quantite','prix')->withTimestamps();
    }

    /**
     * Relation avec DetailsCommande
     */
    public function detailsCommandes()
    {
        return $this->hasMany(DetailsCommande::class);
    }
}
