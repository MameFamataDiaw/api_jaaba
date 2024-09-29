<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = ['montant', 'date', 'statut', 'user_id', 'reference'];

    /**
     * Relation avec Client
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function produits(){
        return $this->belongsToMany(Produit::class, 'details_commandes')->withPivot('quantiteProduit','prixProduit')->withTimestamps();
    }

    /**
     * Relation avec DetailsCommande
     */
    public function detailsCommandes()
    {
        return $this->hasMany(DetailsCommande::class);
    }

    public function retours()
    {
        return $this->hasMany(RetourCommande::class);
    }
}
