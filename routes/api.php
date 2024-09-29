<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RoleController;


// Route pour authentifier un utilisateur
Route::post('/userlogin', [AuthController::class,'login'])->name('auth.store');

// Route pour enregistrer un nouvel utilisateur
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

// Route pour lister le role en excluant celui de l'admin
Route::get('/roles', [RoleController::class, 'index']);

// Route qui Gére la demande de lien de réinitialisation de password
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

// Route qui Gére la réinitialisation du mot de passe
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

// Routes pour les Commandes
Route::controller(OrderController::class)->group(function () {
    Route::get('/orders', 'index');
    Route::post('/orders/store', 'store');
    Route::get('/orders/show/{id}', 'show');
    Route::put('/orders/update/{id}', 'update');
    Route::get('/track-order/{reference}', 'track')->name('order.track');
});

// Route pour filtrer les produits par nom ou par categorie
Route::get('/produits/search', [ProduitController::class, 'search']);

// Route pour récupérer l'adresse l'utilisateur
Route::get('/users/{id}/adresse', [AuthController::class, 'getUseAdressById']);



// Accès public pour la liste et les détails des burgers
Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->only([
        'index', 'show'
]);


Route::middleware('auth:sanctum')->group(function() {

    // Route permettant de deconnecter l'utilisateur
    Route::post('logout', [AuthController::class, 'logout']);

    // Route pour récupérer les données d'un utilisateur par ID
    Route::get('/users/{id}', [AuthController::class, 'show']);

    // Route pour récupérer tous les utilisateurs
    Route::get('/users', [AuthController::class, 'index']);

    // Route pour récupérer le role de user loguer
    Route::get('/role/{id}', [RoleController::class, 'checkUserRole']);

    // Route pour modifier le profile de user
    Route::post('/updateProfile', [AuthController::class, 'updateProfile']);

    // Routes pour OrderController
    Route::controller(OrderController::class)->group(function () {
        Route::put('/orders/cancel/{id}', 'cancel');
        Route::put('/orders/{id}/status',  'updateStatus');
        Route::get('/most-ordered-products', [OrderController::class, 'mostOrderedProducts']);

    });

    Route::get('/getNumberUserByRole',[DashboardController::class, 'index']);

    // Route permettant a un vendeur de creer sa boutique
    Route::apiResource('boutiques', \App\Http\Controllers\BoutiqueController::class);

    // Les routes index et show sont accessibles publiquement
    Route::apiResource('produits', \App\Http\Controllers\ProduitController::class)->except([
    'index', 'show'
]);

});






