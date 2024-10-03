<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoutiqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
//            Récupérer toutes les boutiques
//            $boutiques = Boutique::all();
            $user = Auth::user();

            if ($user->role !== 'admin'){
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $boutiques = Boutique::with('user')->get();

            return response()->json([
                'status' => true,
                'boutiques' => $boutiques
            ], 200);

        }catch (\Exception $e){
            \Log::error('Erreur lors de la récupération des boutiques : ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la récupération des boutiques.",
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = Auth::user(); // Récupérer l'utilisateur connecté

            // Vérifier si l'utilisateur est un administrateur
            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Récupérer une boutique par son ID
            $boutique = Boutique::with('user')->findOrFail($id);

            return response()->json([
                'status' => true,
                'boutique' => $boutique
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des détails de la boutique : ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la récupération des détails de la boutique.",
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $boutique = Boutique::findOrFail($id);
            $user = Auth::user();

            if ($boutique->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($boutique->logo && file_exists(public_path($boutique->logo))) {
                unlink(public_path($boutique->logo));
            }

            $boutique->delete();

            return response()->json([
                'status' => true,
                'message' => "Boutique supprimée avec succès !"
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la boutique : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la suppression de la boutique.",
                'error' => $e->getMessage()
            ], 400);
        }
    }

//    public  function getOrCreateBoutique()
//    {
//        //recuperer l'utilisateur authentifie
//        $user = Auth::user();
//
//        // Vérifier si l'utilisateur est un vendeur
//        if ($user->role !== 'vendeur') {
//            return response()->json([
//                 'status' => false,
//                 'message' => "Seuls les vendeurs peuvent créer une boutique."
//             ], 403);
//        }
//
//        // Vérifier si l'utilisateur a le rôle "vendeur"
//        // if (!$user->hasRole('vendeur')) {
//        //     return response()->json([
//        //         'status' => false,
//        //         'message' => "Seuls les vendeurs peuvent créer une boutique."
//        //     ], 403);
//        // }
//
//        //verifier si le vendeur a deja une boutique associee
//        $boutique = $user->boutique;
//
//        if (!$boutique) {
//            // Créer une nouvelle boutique si elle n'existe pas
//            $boutique = Boutique::create([
//                'user_id' => $user->id
//            ]);
//        }
//
//        return response()->json([
//            'status' => true,
//            'boutique' => $boutique
//        ], 200);
//    }

    public function updateBoutique(Request $request, $id)
    {
        try {
//            // Récupérer l'utilisateur authentifié
//            $user = Auth::user();
//
//            if ($user->role !== 'vendeur') {
//                return response()->json([
//                    'status' => false,
//                    'message' => "Seuls les vendeurs peuvent créer une boutique."
//                ], 403);
//            }

            // Récupérer la boutique du vendeur
//            $boutique = $user->boutique;
            $boutique = Boutique::where('user_id', $id)->first();

            if (!$boutique) {
                return response()->json([
                    'status' => false,
                    'message' => "Aucune boutique n'est associée à cet utilisateur."
                ], 404);
            }

            // Validation des données de la boutique
            $request->validate([
                'nomBoutique' => 'required|max:50',
                'description' => 'required|max:255',
                'adresse' => 'required|max:100',
                'telephone' => 'required|numeric',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Gestion du logo
            if ($request->hasFile('logo')) {
                $logoName = time().'.'.$request->logo->extension();
                $request->logo->move(public_path('images/boutiques'), $logoName);
                // Supprimer l'ancien logo si nécessaire
                if ($boutique->logo && file_exists(public_path('images/boutiques/' . $boutique->logo))) {
                    unlink(public_path('images/boutiques/' . $boutique->logo));
                }
                $boutique->logo = $logoName;
            }

            // Mise à jour des autres informations
            $boutique->update([
                'nomBoutique' => $request->nomBoutique,
                'description' => $request->description,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'logo' => $boutique->logo ?? null
            ]);

            return response()->json([
                'status' => true,
                'message' => "Informations de la boutique mises à jour avec succès.",
                'boutique' => $boutique
            ], 200);
        }catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de la boutique : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la mise à jour de la boutique.",
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function destroyBoutique(string $id)
    {
        try {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            if ($user->role !== 'vendeur') {
                return response()->json([
                    'status' => false,
                    'message' => "Seuls les vendeurs peuvent créer une boutique."
                ], 403);
            }

            // Récupérer la boutique
            $boutique = Boutique::findOrFail($id);

            // Vérifier que la boutique appartient à l'utilisateur connecté
            if ($boutique->user_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => "Vous n'êtes pas autorisé à supprimer cette boutique."
                ], 403);
            }

            // Supprimer l'image du logo s'il existe
            if ($boutique->logo && file_exists(public_path('images/boutiques/' . $boutique->logo))) {
                unlink(public_path('images/boutiques/' . $boutique->logo));
            }

            // Supprimer les produits associés à la boutique
            $boutique->produits()->delete();  // Assurez-vous d'avoir une relation dans le modèle Boutique

            // Supprimer la boutique
            $boutique->delete();

            return response()->json([
                'status' => true,
                'message' => "Boutique supprimée avec succès !"
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la boutique : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la suppression de la boutique.",
                'error' => $e->getMessage()
            ], 400);
        }
    }


}
