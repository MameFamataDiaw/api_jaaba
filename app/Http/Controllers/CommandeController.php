<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\DetailsCommande;
use App\Models\Produit;
use App\Models\RetourCommande;
use App\Notifications\CommandeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            //Valider les donnees de la commande
            $request->validate([
                'produits' => 'required|array',
                'produits.*.id' => 'required|exists:produits,id',
                'produits.*.quantite' => 'required|integer|min:1',
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => "Vous devez être passer une commande."
                ], 403);
            }

            //Creer la commande
            $commande = Commande::create([
                'user_id' => $user->id,
                'montant' => 0,
            ]);

            $montantTotal = 0;

            //Ajouter les details de la commande
            foreach ($request->produits as $produit){
                //verifier que la quantite demandee est disponible
                $produitModel = Produit::find($produit['id']);

                if (!$produitModel) {
                    return response()->json([
                        'status' => false,
                        'message' => "Le produit avec l'ID {$produit['id']} n'existe pas."
                    ], 404);
                }

                if ($produitModel->quantite < $produit['quantite']){
                    return response()->json([
                        'status' => false,
                        'message' => "La quantite demandee pour le produit {$produitModel->libelle} n'est pas disponible."
                    ], 400);
                }

                //Creer un detail de commande
                DetailsCommande::create([
                    'produit_id' => $produit['id'],
                    'commande_id' => $commande->id,
                    'quantiteProduit' => $produit['quantite'],
                    'prixProduit' => $produitModel->prix,
                ]);

                //Mettre a jour le montant total
                $montantTotal += $produitModel->prix * $produit['quantite'];

                //Reduire la quantite du produit
                $produitModel->quantite -= $produit['quantite'];
                $produitModel->save();
            }

            //mise a jour du montant de la commande
            $commande->montant = $montantTotal;
            $commande->save();

            return response()->json([
                'status' => true,
                'message' => "Commande créée avec succès !",
                'commande' => $commande
            ], 201);
        }catch(\Exception $e){
            \Log::error('Erreur lors de la création de la commande : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la création de la commande.",
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
        //
    }
    /**
     * Update commande's statut in storage.
     */
    public function updateStatut(Request $request, $id)
    {
        try {
            $commande = Commande::findOrFail($id);
            $user = Auth::user();

            //verifier que l'utilisateur est le vendeur concerne
            $produits = $commande->detailsCommandes;
            foreach($produits as $detail){
                if ($detail->produit->boutique->user_id != $user->id){
                    return response()->json([
                        'status' => false,
                        'message' => "Vous n'etes pas autorise a changer le statut de cette commande."
                    ], 403);
                }
            }

            $request->validate([
                'statut' => 'required|in:en cours,termine,annule',
            ]);

            //Mise a jour du statut
            $commande->statut = $request->statut;
            $commande->save();

            // Envoi des notifications
            $client = $commande->user;
            $client->notify(new CommandeNotification($commande, $request->statut));

            return response()->json([
                'status' => true,
                'message' => "Le statut de la commande a été mis à jour.",
                'commande' => $commande
            ], 200);
        }catch (\Exception $e){
            \Log::error('Erreur lors de la mise à jour du statut de la commande : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de la mise à jour du statut de la commande.",
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Annuler une commande avant traitement.
     */
    public function annulerCommande($id)
    {
        try {
            $commande = Commande::findOrFail($id);

            //vefier si la commande est toujours en cours
            if ($commande->statut !== 'en cours'){
                return response()->json([
                    'status' => false,
                    'message' => "La commande ne peut plus être annulée."
                ], 403);
            }

            //Mettre a jour le statut de la commande
            $commande->statut = 'annule';
            $commande->save();

            return response()->json([
                'status' => true,
                'message' => "Votre commande a été annulée avec succès.",
                'commande' => $commande
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'annulation de la commande : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Erreur lors de l'annulation de la commande.",
                'error' => $e->getMessage()
            ], 400);
        }

    }

    /**
     * Soummetre une demande de retour
     */
//    public function retournerProduit(Request $request, $id)
//    {
//        try {
//            $commande = Commande::findOrFail($id);
//
//            //Verifier a ete livree
//            if($commande->satut !== 'termine'){
//                return response()->json([
//                    'status' => false,
//                    'message' => "Vous ne pouvez demander un retour que pour les commandes terminées."
//                ], 403);
//            }
//
//            $request->validate([
//                'produit_id' => 'required|exists:produits,id',
//                'motif' => 'required|string|max"255'
//            ]);
//
//            //Creer la demande de retour
//            $retour = RetourCommande::create([
//                'commande_id' => $commande->id,
//                'produit_id' => $request->produit_id,
//                'motif' => $request->motif,
//            ]);
//
//            return response()->json([
//                'status' => true,
//                'message' => "Votre demande de retour a été soumise avec succès.",
//                'retour' => $retour
//            ], 201);
//
//        }catch (\Exception $e){
//            \Log::error('Erreur lors de la demande de retour : ' . $e->getMessage());
//            return response()->json([
//                'status' => false,
//                'message' => "Erreur lors de la demande de retour.",
//                'error' => $e->getMessage()
//            ], 400);
//        }
//    }
}
