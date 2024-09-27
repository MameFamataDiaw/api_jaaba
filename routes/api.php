<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route pour authentifier un utilisateur
//Route::post('/login', [AuthController::class,'store'])->name('auth.store');
Route::post('/userlogin', [AuthController::class,'login'])->name('auth.store');

// Route pour enregistrer un nouvel utilisateur
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// Route pour lister le role en excluant celui de l'admin
Route::get('/roles', [RoleController::class, 'index']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('logout', [AuthController::class, 'logout']);
    // Route pour récupérer les données d'un utilisateur par ID
    Route::get('/users/{id}', [AuthController::class, 'show']);

    // Route pour récupérer tous les utilisateurs
    Route::get('/users', [AuthController::class, 'index']);
    // Route pour récupérer le role de user loguer
    Route::get('/role/{id}', [RoleController::class, 'checkUserRole']);
    // Route pour modifier le profile_photo de user
    Route::post('/user/profile/photo', [AuthController::class, 'updateProfilePhoto']);

    //Boutique
//    Route::apiResource('boutiques', \App\Http\Controllers\BoutiqueController::class);
    // Route pour récupérer ou créer une boutique pour le vendeur authentifié
    Route::get('/boutique', [BoutiqueController::class, 'getOrCreateBoutique'])->name('boutique.getOrCreate');
    // Route pour mettre à jour la boutique du vendeur
    Route::put('/boutique', [BoutiqueController::class, 'updateBoutique'])->name('boutique.update');
    //Route pour permetre au vendeur de supprimer sa boutique
    Route::delete('/boutique/{id}', [BoutiqueController::class, 'destroyBoutique']);
    //Route pour recuperer toutes les boutiques
    Route::get('/boutiques', [BoutiqueController::class, 'index']);
    //Route pour voir les details d'une boutique
    Route::get('/boutiques/{id}', [BoutiqueController::class, 'show']);


    //Produits
//    Route::apiResource('produits', \App\Http\Controllers\ProduitController::class);
    Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->except([
        'index', 'show'  // Les routes index et show sont accessibles publiquement
    ]);
    //lister les produits de la boutique du vendeur authentifie
    Route::get('/boutique/produits', [ProduitController::class, 'getProduitsBoutique'])->middleware('auth:sanctum');


    //Commandes
    Route::apiResource('commandes', \App\Http\Controllers\CommandeController::class);
    //Mise a jour du statut de la commande par le vendeur
    Route::patch('/commandes/{id}/statut', [CommandeController::class, 'updateStatut']);
    //Annulation d'une commande par le client
    Route::post('/commandes/{id}/annuler', [CommandeController::class, 'annulerCommande']);

    //Soumettre une demande de retour
    Route::post('/retours', [\App\Http\Controllers\RetourController::class, 'store']);
});

Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->only([
    'index', 'show'  // Accès public pour la liste et les détails des produits
]);




