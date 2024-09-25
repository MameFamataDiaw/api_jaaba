<?php

namespace App\Http\Controllers;

use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        // Liste des rôles possibles sans 'admin'
        $roles = collect(['client', 'vendeur', 'livreur']);

        return response()->json($roles);
    }


    public function checkUserRole($id)
    {
        // Trouver l'utilisateur par ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Vérifier le rôle de l'utilisateur
        $role = $user->role;

        return response()->json(['user_id' => $id, 'role' => $role]);
    }

}
