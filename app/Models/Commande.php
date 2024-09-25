<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = ['montant', 'date', 'statut', 'client_id',];

    /**
     * Relation avec Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function produits(){
        return $this->belongsToMany(Produit::class, 'details_commandes')->withPivot('quantite','prix')->withTimestamps();
    }

    /**
     * Relation avec DetailsCommande
     */
    public function detailsCommandes()
    {
        return $this->hasMany(DetailsCommande::class);
    }
}
