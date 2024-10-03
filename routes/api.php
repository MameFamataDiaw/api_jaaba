<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RetourController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route pour authentifier un utilisateur
Route::post('/login', [AuthController::class,'login'])->name('auth.store');

// Route pour enregistrer un nouvel utilisateur
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// Route pour lister le role en excluant celui de l'admin
Route::get('/roles', [RoleController::class, 'index']);

// Route pour mettre à jour la boutique du vendeur
Route::put('/boutique/{id}', [BoutiqueController::class, 'updateBoutique'])->name('boutique.update');

//Route pour recuperer toutes les categories
Route::get('/categories', [\App\Http\Controllers\CategorieController::class, 'index']);
//Route pour lister les produits par categorie
Route::get('/categories/{categorie_id}/produits', [\App\Http\Controllers\CategorieController::class, 'produitsParCategorie']);

// Route pour les commandes
Route::controller(OrderController::class)->group(function (){
    //Route permettant de lister toutes les commandes
    Route::get('/orders', 'index');
    //Route permettant au client de passer commande
    Route::post('/orders/store', 'store');
    //Route permettant au client de suivre l'etat de sa commande
    Route::get('/track-order/{reference}', 'track')->name('order.track');
    //Route pour voir les produits les plus commandes
    Route::get('most-ordered-products', [OrderController::class, 'mostOrderedProducts']);
});
//Passer une demande de retour
Route::post('/return-order/{id}', [RetourController::class, 'store'])->name('demanderRetour');

Route::middleware('auth:sanctum')->group(function() {
    //Route permettant de deconnecter l'utilisateur
    Route::post('logout', [AuthController::class, 'logout']);

    // Route pour récupérer les données d'un utilisateur par ID
    Route::get('/users/{id}', [AuthController::class, 'show']);

    // Route pour récupérer tous les utilisateurs
    Route::get('/users', [AuthController::class, 'index']);

    // Route pour récupérer le role de user loguer
    Route::get('/role/{id}', [RoleController::class, 'checkUserRole']);

    // Route pour modifier le profile_photo de user
    Route::post('/user/profile/photo', [AuthController::class, 'updateProfilePhoto']);

    //Routes pour BoutiqueController
    Route::controller(BoutiqueController::class)->group(function (){
        //    Route::apiResource('boutiques', \App\Http\Controllers\BoutiqueController::class);
        // Route pour récupérer ou créer une boutique pour le vendeur authentifié
//        Route::get('/boutique', [BoutiqueController::class, 'getOrCreateBoutique'])->name('boutique.getOrCreate');
        //Route pour permetre au vendeur de supprimer sa boutique
        Route::delete('/boutique/{id}', [BoutiqueController::class, 'destroyBoutique']);
        //Route pour recuperer toutes les boutiques
        Route::get('/boutiques', [BoutiqueController::class, 'index']);
        //Route pour voir les details d'une boutique
        Route::get('/boutiques/{id}', [BoutiqueController::class, 'show']);
    });

    //Routes pour produitController
    Route::controller(ProduitController::class)->group(function () {
//    Route::apiResource('produits', \App\Http\Controllers\ProduitController::class);
        Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->except([
            // Les routes index et show sont accessibles publiquement
            'index', 'show'
        ]);
        //lister les produits de la boutique du vendeur authentifie
        Route::get('/boutique/produits', [ProduitController::class, 'getProduitsBoutique']);
    });

    //Routes pour OrderController
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders/show/{id}', 'show');
        Route::put('/orders/update/{id}', 'update');
        Route::put('/orders/cancel/{id}', 'cancel');
        Route::put('/orders/{id}/status',  'updateStatus');
        //Route pour recuperer les commandes d'un client
        Route::get('/client/orders', 'commandesClient');
        //Route pour recuperer les commandes des produits d'un vendeur
        Route::get('/vendeur/orders', 'commandesVendeur');
    });
    //Traiter une demande retour
    Route::put('/return-order-update/{id}', [RetourController::class, 'update'])->name('traiterRetour');

    Route::get('/getNumberUserByRole', [DashboardController::class]);
});

Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->only([
    'index', 'show'  // Accès public pour la liste et les détails des produits
]);




