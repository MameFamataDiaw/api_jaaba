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
        try {
            $request->validate(
                [
                    'libelle' => 'required|max:20',
                    'description' => 'required|max:100',
                    'prix' => 'required|numeric',
                    'quantite'=> 'required|numeric',
                    'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]
            );
            $imageName = time().'.'.$request->photo->extension();
            $request->photo->move(public_path('images'), $imageName);

//        $product = new Produit();
//        $product->libelle = $request->libelle;
//        $product->description = $request->description;
//        $product->photo = 'images/'.$imageName;
//        $product->save();

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
                'message' => "Produit cree avec succes !",
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
        //
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

//            // Mettre à jour les autres attributs du produit
//            $product->libelle = $request->libelle;
//            $product->description = $request->description;
//            $product->prix = $request->prix;
//            $product->quantite = $request->quantite;
//            $product->categorie_id = $request->categorie_id;
//
//            // Sauvegarder les changements dans la base de données
//            $product->save();

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
}
