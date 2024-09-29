<?php

namespace App\Http\Controllers;

use App\Mail\OrderComplete;
use App\Models\Produit;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use App\Models\Commande;
use App\Models\User;

class OrderController extends Controller
{

    public function store(Request $request)
    {
        // Valider les données de la requête pour la création d'une commande
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'telephone' => 'required|string',
            'Produits' => 'required|array',
            'Produits.*.id' => 'required|integer|exists:produits,id',
            'Produits.*.quantiteProduit' => 'required|integer|min:1',


        ]);

        // Vérifier la disponibilité des produits en stock avant de créer la commande
        $montantTotal = 0;
        foreach ($request->Produits as $Produit) {
            $ProduitInStock = Produit::find($Produit['id']);

            // Vérifier si le produit existe et si la quantité est suffisante
            if (!$ProduitInStock || $ProduitInStock->quantite < $Produit['quantiteProduit']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le produit ' . $ProduitInStock->libelle . ' est en rupture de stock ou la quantité demandée est trop élevée.'
                ], 400);
            }

            $montantTotal += $ProduitInStock->prix * $Produit['quantiteProduit'];

        }
        // Créer ou mettre à jour les informations du client
        $customer = User::updateOrCreate(
            ['email' => $request->email],
            [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'adresse' => $request->adresse,
                'password' => bcrypt($request->password),
                'telephone' => $request->telephone,
            ]
        );

        // Créer une nouvelle commande avec le montant calculé
        $Commande = new Commande([
            'user_id' => $customer->id,
            'montant' => $montantTotal,
            'statut' => 'en cours',
            'reference' => 'ORD-' . uniqid(),
        ]);

        $Commande->save();

        // Ajouter les produits à la commande et mettre à jour le stock
        foreach ($request->Produits as $Produit) {
            $ProduitInStock = Produit::find($Produit['id']);
            $Commande->Produits()->attach($Produit['id'], [
                'quantiteProduit' => $Produit['quantiteProduit'],
                'prixProduit' => $ProduitInStock->prix,
            ]);

        }

        Mail::to($customer->email)->send(new OrderConfirmation($Commande));

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès.',
            'Commande' => $Commande
        ], 201);
    }


    public function index()
    {
        try {
            // Récupérer les commandes avec les informations clients et produits paginées
            $orders = Commande::with('customer', 'produits')
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function track($reference)
    {
        $order = Commande::where('reference', $reference)->first();

        // Charger la relation des produits si elle n'est pas déjà chargée
        $order->load('produits');

        // Préparer les données à renvoyer
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
            'produits' => $order->produits->map(function ($produit) {
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
        $request->validate([
            'statut' => 'required|in:en cours,terminé,annulé',
        ]);

        try {
            $user = auth()->user();

            if ($user->role !== 'vendeur') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Vous devez être un vendeur pour mettre à jour une commande.'
                ], 403);
            }

            $order = Commande::with(['produits'])->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée.'
                ], 404);
            }

            // Vérifier si tous les produits de la commande appartiennent à ce vendeur
            $produitsDuVendeur = $order->produits->filter(function ($produit) use ($user) {
                return $produit->user_id === $user->id;
            });

            // Si aucun produit de la commande n'appartient à ce vendeur
            if ($produitsDuVendeur->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande ne contient aucun produit de votre boutique.'
                ], 403);
            }

            $order->statut = $request->input('statut');

            // Si le statut est "terminé", mettre à jour les quantités des produits en stock
            if ($request->input('statut') === 'terminé') {
                foreach ($produitsDuVendeur as $product) {
                    $productInStock = Produit::find($product->id);

                    if ($productInStock) {
                        $productInStock->quantite -= $product->pivot->quantiteProduit;

                        // Vérifier que la quantité en stock n'est pas négative
                        if ($productInStock->quantite < 0) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Stock insuffisant pour le produit ' . $productInStock->libelle
                            ], 400);
                        }

                        $productInStock->save();
                    }
                }

                // Envoyer un email de confirmation de la commande terminée
                Mail::to($order->customer->email)->send(new OrderComplete($order));
            }

            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de la commande mis à jour avec succès.',
                'order' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function cancel(string $id)
{
    try {
        $order = Commande::find($id);

        if ($order->statut === 'annulé') {
            return response()->json(['success' => false, 'message' => 'Order is already cancelled.']);
        }

        $order->statut = 'annulé';

        $order->save();

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    public function update(Request $request, string $id)
    {
        $request->validate([
            'statut' => 'required|in:en cours,terminé,annulé',
            'produits' => 'required|array',
            'produits.*.id' => 'integer|exists:produits,id',
            'produits.*.quantiteProduit' => 'integer|min:1',
        ]);

        try {

            $order = Commande::find($id);

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Commande non trouvée.'], 404);
            }

            if ($order->statut !== 'en cours') {
                return response()->json([
                    'success' => false,
                    'message' => 'Modification impossible. Seules les commandes avec le statut "en cours" peuvent être modifiées.'
                ], 403);
            }

            $order->statut = $request->statut;
            $montantTotal = 0;
            $order->produits()->detach();
            foreach ($request->produits as $produit) {
                $productInStock = Produit::find($produit['id']);
                if ($productInStock) {
                    $order->produits()->attach($produit['id'], [
                        'quantiteProduit' => $produit['quantiteProduit'],
                        'prixProduit' => $productInStock->prix,
                    ]);
                    $montantTotal += $produit['quantiteProduit'] * $productInStock->prix;
                }
            }
            $order->montant = $montantTotal;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Commande mise à jour avec succès.',
                'order' => $order
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    public function show(string $id)
    {
        try {
            // Récupérer une commande spécifique avec les informations clients et produits associées
            $order = Commande::with('customer', 'produits')->findOrFail($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function mostOrderedProducts()
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'vendeur') {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
            }
            // Récupérer les produits les plus commandés dans la boutique du vendeur avec leur catégorie
            $mostOrderedProducts = Produit::with('categorie')
                ->where('user_id', $user->id)
                ->withCount('commandes')
                ->orderBy('commandes_count', 'desc')
                ->take(9)
                ->get();

            return response()->json($mostOrderedProducts);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
