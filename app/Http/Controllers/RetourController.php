<?php

namespace App\Http\Controllers;

use App\Mail\RetourOrderReceived;
use App\Mail\RetourOrderUpdated;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\RetourCommande;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class RetourController extends Controller
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
    public function store(Request $request, $id)
    {
        $commande = Commande::findOrFail($id);

        // Vérifier que la commande existe et peut être retournée
        if ($commande->statut !== 'livrée') {
            return response()->json(['message' => 'La commande ne peut pas être retournée.'], 400);
        }

        // Vérifier si la commande est dans le délai de retour
        $dateLimite = $commande->created_at->addDays(14); // Exemple : retour autorisé sous 14 jours
        if (now()->greaterThan($dateLimite)) {
            return response()->json(['message' => 'Le délai de retour est expiré.'], 400);
        }

        // Validation du motif de retour
        $request->validate([
            'motif' => 'required|string|max:255',
            'produits' => 'required|array',
            'produits.*.id' => 'required|integer|exists:produits,id',
            'produits.*.quantitéRetournée' => 'required|integer|min:1',
        ]);

        // Créer des entrées dans la table 'retours' pour chaque produit
        $retours = []; // Tableau pour stocker les retours
        foreach ($request->produits as $produit) {
            $retour = RetourCommande::create([
                'commande_id' => $commande->id,
                'produit_id' => $produit['id'],
                'quantitéRetournée' => $produit['quantitéRetournée'],
                'motif' => $request->motif,
                'statut' => 'en attente',
            ]);

            $retours[] = $retour; // Ajouter le retour créé dans le tableau
        }

        // Notifier le client que la demande est en cours de traitement
        // Passer le premier retour créé pour la notification (ou un retour pertinent)
        Mail::to($commande->customer->email)->send(new RetourOrderReceived($retours[0]));

        return response()->json([
            'success' => true,
            'message' => "Votre demande de retour a été reçue et est en cours de traitement.",
        ], 201);
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
        // Récupérer la commande et vérifier le retour
        $commande = Commande::with('produits')->findOrFail($id);

        // Validation des données de retour
        $request->validate([
            'statut' => 'required|in:acceptée,refusée',
            'motif' => 'required_if:statut,refusé|string|max:255',
            'produits' => 'required|array',
            'produits.*.id' => 'required|integer|exists:produits,id',
            'produits.*.quantitéRetournée' => 'required|integer|min:1',
        ]);

        //Traiter chaque produit retourne
        foreach ($request->produits as $produit) {
            // Récupérer l'entrée de retour pour le produit
            $retour = RetourCommande::where('commande_id', $commande->id)
                ->where('produit_id', $produit['id'])
                ->firstOrFail();

            // Mettre à jour le statut du retour
            $updateData = ['statut' => $request->statut];

            //Ajouter le motif si le retour est refuse
            if ($request->statut === 'refusée'){
                $updateData['motif'] = $request->motif;
            }

            $retour->update($updateData);

            // Si la demande de retour est acceptée, remettre en stock
            if ($request->statut === 'acceptée') {
                $produitStock = Produit::find($produit['id']);
                $produitStock->quantite += $produit['quantitéRetournée'];
                $produitStock->save();
            }
        }

        // Notifier le client du statut de la demande (acceptée ou refusée)
        Mail::to($commande->customer->email)->send(new RetourOrderUpdated($retour, $request->statut));

        return response()->json([
            'success' => true,
            'message' => $request->statut === 'acceptée' ? 'Retour accepté et stock mis à jour.' : 'Retour refusé.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
