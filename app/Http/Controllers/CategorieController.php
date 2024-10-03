<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //recuperer toutes les categories
            $categories = Categorie::all();

            return response()->json([
                'success' => true,
                'categories' => $categories
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recuperation se categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les produits par categorie
     */
    public function produitsParCategorie($categorie_id)
    {
        try {
            //verifier si la categorie existe
            $categorie = Categorie::findOrFail($categorie_id);

            //recuperer tous les produits de cette categorie
            $produits = Produit::where('categorie_id', $categorie_id)->get();

            //retourner les produits avec les informations de la categorie
            return response()->json([
                'success' => true,
                'categorie' => $categorie->nomCategorie,
                'produits' => $produits
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recuperation des produits pour cette categorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
