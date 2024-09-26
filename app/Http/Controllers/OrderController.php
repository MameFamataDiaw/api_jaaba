<?php

namespace App\Http\Controllers;

use App\Mail\OrderComplete;
use App\Models\Produit;
use Illuminate\Http\Request;

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
        'password'=>'required|string',
        'adresse' => 'required|string',
        'telephone' => 'required|string',
        'montant' => 'required|integer',
        'Produits' => 'required|array',
        'Produits.*.id' => 'required|integer|exists:produits,id',
        'Produits.*.quantiteProduit' => 'required|integer|min:1',
        'Produits.*.prixProduit' => 'required|integer',
    ]);


    // Vérifier la disponibilité des produits en stock avant de créer la commande
    foreach ($request->Produits as $Produit) {
        $ProduitInStock = Produit::find($Produit['id']);
        if (!$ProduitInStock || $ProduitInStock->quantite < $Produit['quantiteProduit']) {
            return response()->json(['success' => false, 'message' => 'Produit ' . $Produit['id'] . ' is out of stock.']);
        }
    }

    // Utiliser updateOrCreate pour créer ou mettre à jour les informations du client
    $customer = User::updateOrCreate(
        ['email' => $request->email],
        [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'adresse' => $request->adresse,
            'email' => $request->email,
            'password' => $request->password,
            'telephone' => $request->telephone,

        ]
    );

    //dd($customer);

    // Créer une nouvelle commande sans encore attribuer de référence
    $Commande = new Commande([
        'user_id' => $customer->id,
        'montant' => $request->montant,
        'statut' => 'en cours',
        'reference' => 'ORD-' . uniqid(),
    ]);

    $Commande->save();

    // Ajouter les produits à la commande et mettre à jour le stock
    foreach ($request->Produits as $Produit) {
        $Commande->Produits()->attach($Produit['id'], [
            'quantiteProduit' => $Produit['quantiteProduit'],
            'prixProduit' => $Produit['prixProduit']
        ]);
    }

    Mail::to($customer->email)->send(new OrderConfirmation($Commande));

    return response()->json(['success' => true, 'message' => 'Commande creee avec succes.', 'Commande' => $Commande], 201);
}


public function index()
    {
        try {
            // Récupérer les commandes avec les informations clients et produits paginées
//            $orders = Order::with('customer', 'products')->get();
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
        // Valider les données de la requête pour la mise à jour du statut
        $request->validate([
            'statut' => 'required|in:en cours,terminé,annulé',

        ]);

        try {
            // Trouver la commande spécifiée
            $order = Commande::with(['produits'])->find($id);

            // Mettre à jour le statut de la commande
            $order->statut = $request->input('statut');

            // Si le statut est "completed", mettre à jour les quantités des produits en stock
            if ($request->input('statut') === 'terminé') {
                foreach ($order->produits as $product) {

                    // Déduire la quantité commandée du stock
                    $productInStock = Produit::find($product->id);

                    if ($productInStock) {
                        $productInStock->quantite -= $product->pivot->quantiteProduit;

                        // Vérifier que la quantité n'est pas négative
                        if ($productInStock->quantity < 0) {
                            return response()->json(['success' => false, 'message' => 'Stock insuffisant pour le produit ' . $product->name], 400);
                        }

                        // Sauvegarder la mise à jour
                        $productInStock->save();
                    }
                }
                Mail::to($order->customer->email)->send(new OrderComplete($order));
            }

            // Sauvegarder les modifications de la commande
            $order->save();

            return response()->json(['success' => true, 'message' => 'Order updated successfully.', 'order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancel(string $id)
{
    try {
        // Trouver la commande spécifique
        $order = Commande::findOrFail($id);

        // Vérifier si la commande n'est pas déjà annulée
        if ($order->statut === 'annulé') {
            return response()->json(['success' => false, 'message' => 'Order is already cancelled.']);
        }

        // Mettre à jour le statut de la commande à "annulé"
        $order->statut = 'annulé';

        // Sauvegarder les modifications
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function update(Request $request, string $id)
{
    // Valider les données de la requête pour la mise à jour d'une commande
    $request->validate([
        'statut' => 'required|in:en cours,terminé,annulé',
        'montant' => 'nullable|integer',
        'produits' => 'nullable|array',
        'produits.*.id' => 'integer|exists:produits,id',
        'produits.*.quantiteProduit' => 'integer|min:1',
        'produits.*.prixProduit'=> 'integer'
    ]);

    try {
        // Trouver la commande spécifiée
        $order = Commande::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Commande non trouvée.'], 404);
        }

        // Mettre à jour les champs de la commande
        $order->update($request->only(['statut', 'montant']));

        // Si des produits sont passés dans la requête, mettre à jour la relation avec les produits
        if ($request->has('produits')) {
            $order->produits()->detach(); // Supprimer les produits actuels
            foreach ($request->produits as $produit) {
                $order->produits()->attach($produit['id'], [
                    'quantiteProduit' => $produit['quantiteProduit'],
                    'prixProduit' => $produit['prixProduit']
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Commande mise à jour avec succès.', 'order' => $order]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
}
