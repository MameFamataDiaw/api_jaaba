<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailsCommande extends Model
{
    use HasFactory;

    protected $fillable = ['produit_id', 'commande_id', 'quantiteProduit', 'prixProduit',];

    /**
     * Relation avec Produit
     */
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Relation avec Commande
     */
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
