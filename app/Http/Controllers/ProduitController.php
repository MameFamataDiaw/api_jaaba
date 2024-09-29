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
        if (!$produits) {
            return response()->json([
                'status' => false,
                'message' => "L'utilisateur n'a pas encore de produits."
            ], 404);
        }

        return response()->json([
            'status' => true,
            'produits' => $produits
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => "Utilisateur non authentifié.",
            ], 401);
        }

        if ($user->role !== 'vendeur') {
            return response()->json([
                'status' => false,
                'message' => "Seuls les utilisateurs avec le rôle vendeur peuvent ajouter des produits.",
            ], 403);
        }

        try {
            $request->validate(
                [
                    'libelle' => 'required',
                    'description' => 'required',
                    'prix' => 'required|numeric',
                    'quantite'=> 'required|numeric',
                    'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]
            );

            // Vérification si la photo existe dans la requête
            $imageName = null;
            if ($request->hasFile('photo')) {
                $imageName = time().'.'.$request->photo->extension();
                $request->photo->move(public_path('images'), $imageName);
            }

            // Création du produit
            $produit = Produit::create([
                'libelle' => $request->libelle,
                'description' => $request->description,
                'prix' => $request->prix,
                'quantite' => $request->quantite,
                'photo' => $imageName,
                'categorie_id' => $request->categorie_id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => "Produit créé avec succès !",
                'produit' => $produit
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'ajout du nouveau produit : '.$e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de l'ajout du nouveau produit.",
                'error' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Produit::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = Auth::user();  // Récupérer l'utilisateur connecte
//            $product = $user->produits()->findOrFail($id);  // Vérifier si le produit appartient bien au vendeur
            // Récupérer le produit à mettre à jour
            $product = Produit::findOrFail($id);
            // Validation des données (photo n'est pas obligatoire)
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
//            $product = Produit::findOrFail($id);
            $user = Auth::user();  // Récupérer l'utilisateur connecté
            $product = $user->produits()->findOrFail($id);  // Vérifier si le produit appartient bien au vendeur

            if ($product->photo && file_exists(public_path('images/' . $product->photo))) {
                unlink(public_path('images/' . $product->photo));
            }

            $product->delete();
//            return response()->json(null,204);
                return response()->json([
                    'status' => true,
                    'message' => "produit supprime !",
                ],204);
        }catch(ModelNotFoundException $ex){
            return response()->json(['error' => 'Produit non trouve'], 404);
        }
    }

    public function search(Request $request)
{
    $query = Produit::query();

    if ($request->has('libelle')) {
        $query->where('libelle', 'like', '%' . $request->input('libelle') . '%');
    }

    if ($request->has('categorie_id')) {
        $query->where('categorie_id', $request->input('categorie_id'));
    }

    $products = $query->with('categorie')->paginate(10);

    return response()->json($products);
}



}
