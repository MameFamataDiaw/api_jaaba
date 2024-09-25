<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route pour authentifier un utilisateur
Route::post('/login', [AuthController::class,'store'])->name('auth.store');
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




});

Route::apiResource('produits', \App\Http\Controllers\ProduitController::class);
Route::apiResource('boutiques', \App\Http\Controllers\BoutiqueController::class);
