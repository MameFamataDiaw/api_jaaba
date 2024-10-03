<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produits = Produit::all();

        return response()->json([
            'status' => true,
            'produits' => $produits
        ],200);
    }

    /**
     * Display a listing of the resource.
     */
    public function getProduitsBoutique()
    {
        try {
            //recuperer l'utilisateur authentifie
            $user = Auth::user();

            //Recuperer la boutique de cet utilisateur
            $boutique = $user->boutique;

            //verifier si la boutique existe
            if (!$boutique){
                return response()->json([
                    'status' => false,
                    'message' => "Vous n'avez pas encore de boutique associée."
                ], 404);
            }

            //Recuperer tous les produits de cette boutique
            $produits = $boutique->produits;

            if ($produits->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => "Votre boutique ne contient pas encore de produits."
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => "Liste des produits de la boutique.",
                'produits' => $produits
            ], 200);

        }catch(\Exception $e){
            \Log::error('Erreur lors de la récupération des produits de la boutique : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la récupération des produits de la boutique.",
                'error' => $e->getMessage()
                ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Vérifier si l'utilisateur a le rôle "vendeur"
            if ($user->role !== 'vendeur') {
                return response()->json([
                    'status' => false,
                    'message' => "Seuls les vendeurs peuvent créer une boutique."
                ], 403);
            }

            $boutique = $user->boutique;

            // Vérifier que la boutique existe pour l'utilisateur connecté
            if (!$boutique) {
                return response()->json([
                    'status' => false,
                    'message' => "Vous devez d'abord créer une boutique avant d'ajouter des produits."
                ], 403);
            }

            $request->validate(
                [
                    'libelle' => 'required|max:20',
                    'description' => 'required|max:100',
                    'prix' => 'required|numeric',
                    'quantite'=> 'required|numeric',
                    'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'categorie_id' => 'required|exists:categories,id'
                    ]
            );

            // Gestion de l'upload de l'image
            $imageName = time().'.'.$request->photo->extension();
            $request->photo->move(public_path('images'), $imageName);

            // Création du produit et association avec la boutique du vendeur
            $produit = Produit::create([
                'libelle' => $request->libelle,
                'description' => $request->description,
                'prix' => $request->prix,
                'quantite' => $request->quantite,
                'photo' => $imageName,
                'categorie_id' => $request->categorie_id,
                'boutique_id' => $boutique->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => "Un nouveau produit est ajoute a la boutique !",
                'produit' => $produit
            ],201);
        }catch (\Exception $e){
            \Log::error('Erreur lors de l\'ajout du nouveau produit  : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de l'ajout du nouveau produit .",
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Récupérer la boutique par ID
            $produit = Produit::findOrFail($id);

            return response()->json([
                'status' => true,
                'produit' => $produit
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Produit non trouvé.",
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Récupérer l'utilisateur connecte
            $user = Auth::user();

            if ($user->role !== 'vendeur') {
                return response()->json([
                    'status' => false,
                    'message' => "Seuls les vendeurs peuvent créer une boutique."
                ], 403);
            }

//            $product = $user->produits()->findOrFail($id);Vérifier si le produit appartient bien au vendeur

            //Recuperer la boutique du vendeur connecte
            $boutique = $user->boutique;

            //Recuperer le produit et verifier qu'il appartient a la boutique du vendeur
            $product = Produit::where('id', $id)
                ->where('boutique_id', $boutique->id)
                ->firstOrFail();

            // Validation des données
            $request->validate([
                'libelle' => 'required|max:20',
                'description' => 'required|max:100',
                'prix' => 'required|numeric',
                'quantite'=> 'required|numeric',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Gérer l'upload de la nouvelle image si elle est présente dans la requête
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne image si elle existe
                if ($product->photo && file_exists(public_path('images/' . $product->photo))) {
                    unlink(public_path('images/' . $product->photo));
                }

                // Enregistrer la nouvelle image
                $imageName = time().'.'.$request->photo->extension();
                $request->photo->move(public_path('images'), $imageName);
                $product->photo = $imageName; // Assigner la nouvelle image
            }

//            Mise a jour des autres champs
            $product->update([
                'libelle' => $request->libelle,
                'description' => $request->description,
                'prix' => $request->prix,
                'quantite' => $request->quantite,
                'categorie_id' => $request->categorie_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => "Produit mis à jour avec succès !",
                'product' => $product
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour du produit : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la mise à jour du produit.",
                'error' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
//            Recuperer l'utilisateur connecte
            $user = Auth::user();
            // Vérifier si l'utilisateur a le rôle "vendeur"
//            if (!$user->hasRole('vendeur')) {
//                return response()->json([
//                    'status' => false,
//                    'message' => "Seuls les vendeurs peuvent créer une boutique."
//                ], 403);
//            }

            if ($user->role !== 'vendeur') {
                return response()->json([
                    'status' => false,
                    'message' => "Seuls les vendeurs peuvent créer une boutique."
                ], 403);
            }
//            $product = $user->produits()->findOrFail($id);  // Vérifier si le produit appartient bien au vendeur

//            Recuperer la boutique du vendeur
            $boutique = $user->boutique;

//            Recuperer le produit et verifier qu'il appartient a la boutique
            $product = Produit::where('id', $id)
                ->where('boutique_id', $boutique->id)
                ->firstOrFail();

//            Supprimer l'image associee au produit si elle existe
            if ($product->photo && file_exists(public_path('images/' . $product->photo))) {
                unlink(public_path('images/' . $product->photo));
            }

//            Supprimer le produit
            $product->delete();

            return response()->json([
                'status' => true,
                'message' => "produit supprime !",
            ],204);

        }catch(ModelNotFoundException $ex){
            return response()->json(['error' => 'Produit non trouve'], 404);
        }
    }
}
