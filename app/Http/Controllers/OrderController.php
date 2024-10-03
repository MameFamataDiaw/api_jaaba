<?php

namespace App\Http\Controllers;

use App\Mail\OrderComplete;
use App\Mail\OrderConfirmation;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //Recuperer les commandes avec les informations clients et produits pagines
            $orders = Commande::with('customer', 'produits')
                ->orderBy('created_at', 'desc')->get();
            return response()->json($orders);
        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                ], 500);
        }
    }

    /**
     * Recuperer toutes les commandes d'un client
     */
    public function commandesClient()
    {
        try {
            //recuperer l'utilisateur authentifie
            $user = Auth::user();

            //verifier si c'est un client
            if ($user->role !== 'client'){
                return response()->json([
                    'success' => false,
                    'message' => "Seuls les clients peuvent voir leurs commandes"
                ], 403);
            }

            //Recuperer toutes les commandes de ce client
            $commandes = Commande::where('user_id', $user->id)->with('produits')->get();

            //Retourner les commandes
            return response()->json([
                'success' => true,
                'client' => $user->nom . ' ' . $user->prenom,
                'commandes' => $commandes
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes du client.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recuperer toutes les commandes contenant les produits du vendeur
     */
    public function commandesVendeur()
    {
        try {
            //Recuperer l'utilisateur authentifie
            $user = Auth::user();

            //Verifier si c'est un vendeur
            if ($user->role !== 'vendeur') {
                return response()->json([
                    'success' => false,
                    'message' => "Seuls les vendeurs peuvent voir leurs commandes."
                ], 403);
            }

            //Recuperer tous les produits du vendeur
            $produitsVendeur = Produit::where('user_id', $user->id)->pluck('id')->toArray();

            //Recperer toutes les commandes contenant ces produits
            $commandes = Commande::whereHas('produits', function ($query) use ($produitsVendeur) {
                $query->whereIn('produit_id', $produitsVendeur);
            })->with('produits')->get();

            //Retourner les commandes
            return response()->json([
                'success' => true,
                'vendeur' => $user->nom . ' ' . $user->prenom,
                'commandes' => $commandes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes du vendeur.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Valider les donnees de la requete pour la creation d'une commande
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'adresse' => 'required|string',
            'telephone' => 'required|string',
            'produits' => 'required|array',
            'produits.*.id' => 'required|integer|exists:produits,id',
            'produits.*.quantiteProduit' => 'required|integer|min:1',
            'produits.*.prixProduit' => 'required|integer',

        ]);

        //Verifier la disponibilite des produits avant de creer la commande
        foreach ($request->produits as  $produit) {
            $produitInStock = Produit::find($produit['id']);
            if (!$produitInStock || $produitInStock->quantite < $produit['quantiteProduit']){
                return response()->json(['success' => false,
                    'message' => 'Produit ' . $produit['id'] . ' is out of stock.'
                ]);
            }
        }

        //Utiliser updateOrcreate pour mettre a jour les informations du client
        $customer = User::updateOrCreate(
            ['email' => $request->email],
            [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'adresse' => $request->adresse,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'telephone' => $request->telephone,
                'role' => 'client'
            ]
        );

        //Calcul du montant total
        $totalAmount = 0;
        foreach ($request->produits as $produit){
            $totalAmount += $produit['quantiteProduit'] * $produit['prixProduit'];
        }

        //Creer une nouvelle commande sans encore attribuer de reference
        $commande = new Commande([
            'user_id' => $customer->id,
            'montant' => $totalAmount,
            'statut' => 'en cours',
            'reference' => 'ORD-' . uniqid(),
        ]);

        $commande->save();

        //Ajouter les produits a la commande et mettre a jour le stock
        foreach ($request->produits as $produit){
            $commande->produits()->attach($produit['id'],
                ['quantiteProduit' => $produit['quantiteProduit'],
                    'prixProduit' => $produit['prixProduit']
                ]);
        }

        Mail::to($customer->email)->send(new OrderConfirmation($commande));

        return response()->json([
            'success' => true,
            'message' => 'Commande creee avec succes.',
            'Commande' => $commande
        ], 201);
    }

    public function track($reference)
    {
        $order = Commande::where('reference', $reference)->first();

        //Charger la relation des produits si elle n'est pas deja chargee.
        $order->load('produits');

        //Preparer les donnees a rencoyer
        $orderData = [
            'reference' => $order->reference,
            'statut' => $order->statut,
            'created_at' => $order->created_at->format('d/m/Y'),
            'montant' => number_format($order->montant, 0, ',', ' ') . ' FCFA',
            'customer' => [
                'nom' => $order->customer->nom,
                'prenom' => $order->customer->prenom,
                'email' => $order->customer->email,
            ],
            'produits' => $order->produits->map(function ($produit){
                return [
                    'libelle' => $produit->libelle,
                    'quantite' => $produit->pivot->quantiteProduit,
                    'prix' => number_format($produit->pivot->prixProduit, 0, ',', ' ') . ' FCFA',
                ];
            }),
        ];
        return response()->json($orderData);
    }

    public function updateStatus(Request $request, string $id)
    {
        //Valider les donnees de la requete pour la mise a jour du statut
        $request->validate([
            'statut' => 'required|in:en cours,terminée,annulée,livrée',
        ]);

        try {
            //Trouver la commnade specifiee
            $order = Commande::with(['produits'])->find($id);

            //Mettre a jour le statut de la commande
            $order->statut = $request->input('statut');

            //Si le statut est 'completed', mettre a jour les quantites des produits en stock
            if ($request->input('statut') === 'terminée'){
                foreach($order->produits as $product){
                    //Reduire la quantite commandee du stock
                    $productInStock = Produit::find($product->id);
                    if ($productInStock){
                        $productInStock->quantite -= $product->pivot->quantiteProduit;

                        // Verifier si la quantite est negative
                        if ($productInStock->quantite < 0){
                            return response()->json([
                                'success' => false,
                                'message' => 'Stock insuffisant pour le produit ' . $product->libelle
                            ], 400);
                        }

                        //Sauvegarder la mise a jour
                        $productInStock->save();
                    }
                }
                Mail::to($order->customer->email)->send(new OrderComplete($order));
            }

            //Sauvegarder les modifications de la commande
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully.', 'order' => $order
            ]);
        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(string $id)
    {
        try {
            //Trouver la commande specifique
            $order = Commande::findOrFail($id);

            //Verifier si la commande n'est pas deja annulee
            if ($order->statut === 'annulée') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already cancelled.'
                ]);
            }

            //Mettre a jour le statut de la commande a annule
            $order->statut = 'annulée';

            //Sauvegarder les modifications
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully.'
            ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Récupérer la commande spécifique avec les informations clients et produits associées
            $commande = Commande::with('customer', 'produits')->findOrFail($id);

            // Récupérer l'utilisateur connecté
            $user = auth()->user();

            // Vérifier si l'utilisateur est un vendeur
            if ($user->role === 'vendeur') {
                // Filtrer les produits pour ne garder que ceux qui appartiennent à la boutique du vendeur connecté
                $produitsFiltrés = $commande->produits->filter(function ($produit) use ($user) {
                    return $produit->boutique->user_id == $user->id;
                });

                // Calculer le montant total uniquement pour les produits du vendeur connecté
                $montantFiltré = $produitsFiltrés->sum(function ($produit) {
                    return $produit->pivot->quantiteProduit * $produit->pivot->prixProduit;
                });

                // Mettre à jour les détails de la commande avec les produits filtrés et le nouveau montant
                $commande->montant = $montantFiltré;
                $commande->setRelation('produits', $produitsFiltrés->values());
            }

            // Retourner la commande avec les produits filtrés si c'est un vendeur, sinon tous les produits
            return response()->json($commande);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Valider les donnees de la requete pour la mise a jour d'une commande
        $request->validate([
            'statut' => 'required|in:en cours,terminée,annulée','livrée',
            'montant' => 'nullable|integer',
            'produits' => 'nullable|array',
            'produits.*.id' => 'integer|exists:produits,id',
            'produits.*.quantite' => 'integer|min:1',
            'produits.*.prixProduit' => 'integer'
        ]);

        try {
            //Trouver la commande specifiee
            $order = Commande::find($id);

            if (!$order){
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvee.'
                ], 404);
            }

            // Mettre à jour les champs de la commande
            $order->update($request->only(['statut', 'montant']));
            // Si des produits sont passés dans la requête, mettre à jour la relation avec les produits
            if ($request->has('produits')) {
                $order->produits()->detach(); // Supprimer les produits actuels
                foreach ($request->produits as $produit) {
                    $order->produits()->attach($produit['id'], [
                        'quantiteProduit' => $produit['quantite'],
                        'prixProduit' => $produit['prix']
                    ]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Commande mise à jour avec succès.', 'order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
