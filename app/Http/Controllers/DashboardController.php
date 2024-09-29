<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //Count the number of users by their roles
            $vendeurCount = User::where('role', 'vendeur')->count();
            $clientCount = User::where('role', 'client')->count();
            $livreurCount = User::where('role', 'livreur')->count();

            //Get the number of categories and products
            $categorieCount = Categorie::count();
            $produitCount = Produit::count();
            $shopCount = Boutique::count();

            //Get the number of orders by status
            $commandesEnCoursCount = Commande::where('statut', 'en cours')->count();
            $commandesTermineCount = Commande::where('statut', 'termine')->count();
            $commandesAnnuleCount = Commande::where('statut', 'annule')->count();

            //return the statistics as a JSON response
            return response()->json([
                'success' => true,
                'data' => [
                    'vendeurCount' => $vendeurCount,
                    'clientCount' => $clientCount,
                    'livreurCount' => $livreurCount,
                    'categorieCount' => $categorieCount,
                    'produitCount' => $produitCount,
                    'commandesEnCoursCount' => $commandesEnCoursCount,
                    'commandesTermineCount' => $commandesTermineCount,
                    'commandesAnnuleCount' => $commandesAnnuleCount,
                    'ShopCount' => $shopCount
                ]
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
