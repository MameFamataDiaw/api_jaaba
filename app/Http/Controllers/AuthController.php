<?php

namespace App\Http\Controllers;

use App\Models\Adresse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\LoginRequest;

use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    function register(Request $request)
    {
        try {
            // Valider les données d'entrée
            $data = $request->validate([
                'nom' => 'required|string',
                'prenom' => 'required|string',
                'telephone' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'required|string',
                'role' => 'required|in:vendeur,client,livreur,admin',
                'pays' => 'required|string',
                'ville' => 'required|string',
                'codePostal' => 'required|string',
            ]);

            // Vérification et enregistrement de la photo
            if ($request->hasFile('photo')) {
                $photoName = time() . '.' . $request->photo->extension();
                $photoPath = $request->file('photo')->storeAs('photos', $photoName, 'public');
                $data['photo'] = $photoPath;
            } else {
                $data['photo'] = null;
            }

            // Créer l'adresse
            $adresse = Adresse::create([
                'pays' => $request->pays,
                'ville' => $request->ville,
                'codePostal' => $request->codePostal,
            ]);

            // Créer un nouvel utilisateur
            $user = new User();
            $user->nom = $data['nom'];
            $user->prenom = $data['prenom'];
            $user->telephone = $data['telephone'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->photo = $data['photo'];
            $user->role = $data['role'];
            $user->statut = true;
            $user->adresse_id = $adresse->id; // Associer l'adresse à l'utilisateur

            $user->save();

            return response()->json([
                'message' => 'User registered successfully !',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'role' => $user->role,
                    'statut' => $user->statut,
                    'adresse_id' => $user->adresse_id,
                    'adresse' => [
                        'pays' => $adresse->pays,
                        'ville' => $adresse->ville,
                        'codePostal' => $adresse->codePostal,
                    ],
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while registering the user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            // Valider les données d'entrée
            $credentials = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Vérifier les informations d'identification (email et mot de passe)
            if (!Auth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Générer un token pour l'utilisateur
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'adresse'=>$user->adresse,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Login failed: ' . $e->getMessage()], 400);
        }
    }

    public function logout(Request $request)
    {
        // Revoquer le token d'authentification actuel (pour Sanctum)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'You have successfully logged out!',
        ], 200);
    }

    public function show($id)
    {
        try {
            $user = User::with('adresse')->find($id);

            return response()->json([
                'status' => 200,
                'message' => 'Utilisateur trouvé avec succès !',
                'results' => [
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'telephone' => $user->telephone,
                    'email' => $user->email,
                    'adresse_id' => $user->adresse_id
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur non trouvé.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function index()
    {
        try {
            $users = User::all();

            return response()->json([
                'status' => 200,
                'message' => 'Utilisateurs récupérés avec succès !',
                'results' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur s\'est produite lors de la récupération des utilisateurs.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updateProfile(Request $request)
    {
        // Valider uniquement les champs présents dans la requête
        $validatedData = $request->validate([
            'nom' => 'nullable|string|max:30',
            'prenom' => 'nullable|string|max:50',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . auth()->id(),
            'photo' => 'nullable|string',
        ]);

        try {
            // Récupérer l'utilisateur connecté
            $user = auth()->user();

            // Mettre à jour uniquement les champs fournis
            if ($request->filled('nom')) {
                $user->nom = $request->nom;
            }

            if ($request->filled('prenom')) {
                $user->prenom = $request->prenom;
            }

            if ($request->filled('telephone')) {
                $user->telephone = $request->telephone;
            }

            if ($request->filled('adresse')) {
                $user->adresse = $request->adresse;
            }

            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            if ($request->hasFile('photo')) {
                $imageName = time() . '.' . $request->photo->extension();
                $request->photo->move(public_path('images/profiles'), $imageName);
                $dataToUpdate['photo'] = $imageName;
            }


            // Sauvegarder les modifications
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getUseAdressById($id)
    {
        try {

            $user = User::find($id);

            if (!$user->adresse) {
                return response()->json([
                    'message' => 'L\'utilisateur n\'a pas d\'adresse associée.'
                ], 404);
            }

            return response()->json([
                'message' => 'Adresse récupérée avec succès.',
                'user_id'=>$user->id,
                'adresse' => [
                    'pays' => $user->adresse->pays,
                    'ville' => $user->adresse->ville,
                    'codePostal' => $user->adresse->codePostal,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de l\'adresse.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





}
