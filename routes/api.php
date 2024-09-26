<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route pour authentifier un utilisateur
Route::post('/userlogin', [AuthController::class,'login'])->name('auth.store');

// Route pour enregistrer un nouvel utilisateur
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// Route pour lister le role en excluant celui de l'admin
Route::get('/roles', [RoleController::class, 'index']);


// Routes pour les Commandes
Route::controller(OrderController::class)->group(function () {
    Route::get('/orders', 'index');
    Route::post('/orders/store', 'store');
    Route::get('/track-order/{reference}', 'track')->name('order.track');
    Route::get('most-ordered-products', [OrderController::class, 'mostOrderedProducts']);


});



Route::middleware('auth:sanctum')->group(function() {

    // Route permettant de deconnecter l'utilisateur
    Route::post('logout', [AuthController::class, 'logout']);

    // Route pour récupérer les données d'un utilisateur par ID
    Route::get('/users/{id}', [AuthController::class, 'show']);

    // Route pour récupérer tous les utilisateurs
    Route::get('/users', [AuthController::class, 'index']);

    // Route pour récupérer le role de user loguer
    Route::get('/role/{id}', [RoleController::class, 'checkUserRole']);

    // Route pour modifier le profile_photo de user
    Route::post('/user/profile/photo', [AuthController::class, 'updateProfilePhoto']);

    // Routes pour OrderController
    Route::controller(OrderController::class)->group(function () {

        Route::get('/orders/show/{id}', 'show');
        Route::put('/orders/update/{id}', 'update');
        Route::put('/orders/cancel/{id}', 'cancel');
        Route::put('/orders/{id}/status',  'updateStatus');


    });

    Route::get('/getNumberUserByRole',[DashboardController::class, 'index']);


    // Route permettant a un vendeur de creer sa boutique
    Route::apiResource('boutiques', \App\Http\Controllers\BoutiqueController::class);

    // Route permettant a un vendeur d'ajouter de produits dans sa boutique
    Route::post('/addProd', [ProduitController::class, 'store']);





});


// Accès public pour la liste et les détails des burgers
Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->only([
    'index', 'show'
]);

// Les routes index et show sont accessibles publiquement
Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->except([
    'index', 'show'
]);
