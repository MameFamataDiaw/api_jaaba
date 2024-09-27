<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\RetourCommande;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'produit_id' => 'required|exists:produits,id',
            'quantite_retournee' => 'required|numeric|min:1',
            'motif' => 'nullable|string',
        ]);

        $commande = Commande::find($request->commande_id);

        //Verifier si la commande peut etre retournee
        if(now()->diffInDays($commande->date) > 14){
            return response()->json([
                'status' => false,
                'message' => 'Le délai de retour est dépassé.'
            ], 403);
        }

        //Creer le retour
        $retour = RetourCommande::create([
            'commande_id' => $request->commande_id,
            'produit_id' => $request->produit_id,
            'quantite_retournee' => $request->quantite_retournee,
            'motif' => $request->motif,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Retour initié avec succès.',
            'retour' => $retour,
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
