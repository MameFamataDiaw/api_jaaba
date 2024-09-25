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

//        $user = Auth::user();
//        $boutique = $user->boutique;
//
//        if (!$boutique) {
//            return response()->json([
//                'status' => false,
//                'message' => "L'utilisateur n'a pas encore de boutique."
//            ], 404);
//        }
//
//        return response()->json([
//            'status' => true,
//            'boutique' => $boutique
//        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        $user = Auth::user();
//        try {
//            $request->validate([
//                'nomBoutique' => 'required|max:50',
//                'description' => 'required',
//                'adresse' => 'required|max:100',
//                'telephone' => 'required|numeric',
//                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            ]);
//
//            $logoName = null;
//            if ($request->hasFile('logo')){
//                $logoName = time().'.'.$request->logo->extension();
//                $request->logo->move(public_path('images/boutiques'), $logoName);
//            }
//
//            $boutique = Boutique::create([
//                'nomBoutique' => $request->nomBoutique,
//                'description' => $request->description,
//                'adresse' => $request->adresse,
//                'telephone' => $request->telephone,
//                'logo' => $logoName ? 'images/boutiques/'.$logoName : null,
//                'user_id' => $user->id,
//            ]);
//
//            return response()->json([
//                'status' => true,
//                'message' => "Boutique cree avec succes !",
//                'boutique' => $boutique
//            ], 201);
//
//        }catch(\Exception $e){
//            \Log::error('Erreur lors de la création de la boutique : '.$e->getMessage());
//            return response()->json([
//                'status' => false,
//                'message' => "Erreur lors de la création de la boutique.",
//                'error' => $e->getMessage()
//            ], 400);
//        }
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
//        try {
//            // Récupérer la boutique à mettre à jour
//            $boutique = Boutique::findOrFail($id);
//            $user = Auth::user();
//
//            if ($boutique->user_id !== $user->id){
//                return response()->json(['error' => 'Non autorisé.'], 403);
//            }
//
//            // Validation des données
//            $request->validate([
//                'nomBoutique' => 'required|max:50',
//                'description' => 'required',
//                'adresse' => 'required|max:100',
//                'telephone' => 'required|numeric',
//                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            ]);
//
//            // Gérer l'upload de la nouvelle image si elle est présente dans la requête
//            if ($request->hasFile('logo')) {
//                // Supprimer l'ancienne image si elle existe
//                if ($boutique->logo && file_exists(public_path($boutique->logo))) {
//                    unlink(public_path($boutique->logo));
//                }
//
//                // Enregistrer la nouvelle image
//                $logoName = time().'.'.$request->logo->extension();
//                $request->logo->move(public_path('images/boutiques'), $logoName);
//                $boutique->logo = 'images/boutiques/' . $logoName; // Assigner la nouvelle image
//            }
//
////            $boutique->update([
////                'nomBoutique' => $request->nomBoutique,
////                'description' => $request->description,
////                'adresse' => $request->adresse,
////                'telephone' => $request->telephone,
////            ]);
//
//            // Mettre à jour les autres attributs de la boutique
//            $boutique->nomBoutique = $request->nomBoutique;
//            $boutique->description = $request->description;
//            $boutique->adresse = $request->adresse;
//            $boutique->telephone = $request->telephone;
//
//            // Sauvegarder les changements dans la base de données
//            $boutique->save();
//
//            return response()->json([
//                'status' => true,
//                'message' => "Boutique mis à jour avec succès !",
//                'boutique' => $boutique
//            ], 200);
//
//        } catch (\Exception $e) {
//            \Log::error('Erreur lors de la mise à jour de la boutique : '.$e->getMessage());
//            return response()->json([
//                'status' => false,
//                'message' => "Erreur lors de la mise à jour de la boutique.",
//                'error' => $e->getMessage()
//            ], 400);
//        }
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

    public  function getOrCreateBoutique()
    {
        //recuperer l'utilisateur authentifie
        $user = Auth::user();

        //verifier si le vendeur a deja une boutique associee
        $boutique = $user->boutique;

        if (!$boutique) {
            // Créer une nouvelle boutique si elle n'existe pas
            $boutique = Boutique::create([
//                'nomBoutique' => 'Nouvelle Boutique',
//                'description' => 'Description par défaut',
//                'logo' => null,
//                'adresse' => 'Adresse par défaut',
//                'telephone' => '0000000000',
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'status' => true,
            'boutique' => $boutique
        ], 200);
    }

    public function updateBoutique(Request $request)
    {
        try {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Récupérer la boutique du vendeur
            $boutique = $user->boutique;

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

}
