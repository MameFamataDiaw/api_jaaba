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
    public function index()
    {
        try {
            // Count the number of users by their roles
            $vendeurCount = User::where('role', 'vendeur')->count();
            $clientCount = User::where('role', 'client')->count();
            $livreurCount = User::where('role', 'livreur')->count();

            // Get the number of categories and products
            $categorieCount = Categorie::count();
            $produitCount = Produit::count();
            $ShopCount = Boutique::count();

            // Get the number of orders by status
            $commandesEnCoursCount = Commande::where('statut', 'en cours')->count();
            $commandesTermineCount = Commande::where('statut', 'terminé')->count();
            $commandesAnnuleCount = Commande::where('statut', 'annulé')->count();

            // Return the statistics as a JSON response
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
                    'ShopCount' =>$ShopCount
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
