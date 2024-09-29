<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'adresse_id',
        'email',
        'photo',
        'password',
        'role',
        'statut',
    ];

    public function adresse()
    {
        return $this->belongsTo(Adresse::class, 'adresse_id');
    }

    public function customer()
{
    return $this->belongsTo(User::class, 'user_id');
}


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function boutique()
    {
        return $this->hasOne(Boutique::class); // Si la relation est one-to-one
        // return $this->hasMany(Boutique::class); // Si un utilisateur peut avoir plusieurs boutiques
    }

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
