<?php

namespace App\Http\Controllers;

use App\Models\Boutique;
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
                'nom' => 'required|string|max:30',
                'prenom' => 'required|string|max:50',
                'telephone' => 'required|string',
                'adresse' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'required|string|min:6',
                'role' => 'required|in:vendeur,client,livreur,admin',
            ]);

            // Vérification et enregistrement de la photo
            if ($request->hasFile('photo')) {
                $photoName = time() . '.' . $request->photo->extension();
                $photoPath = $request->file('photo')->storeAs('photos', $photoName, 'public');
                $data['photo'] = $photoPath;
            }

            // Créer un nouvel utilisateur
            $user = new User();
            $user->nom = $data['nom'];
            $user->prenom = $data['prenom'];
            $user->telephone = $data['telephone'];
            $user->adresse = $data['adresse'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->photo = $data['photo'];
            $user->role = $data['role'];
            $user->statut = true;

            $user->save();

            //Si le role est 'vendeur', on lui cree automatiquement une boutique
            if ($request->role === 'vendeur'){
                Boutique::create([
                    'user_id' => $user->id,
                ]);

                return response()->json([
                    'message' => 'Inscription reussie. Veuillez completez les informations de votre boutique.',
                    'user_id' => $user->id, //L'ID utilisateur pour permettre la modification de la boutique plus tard.
                ], 201);
            }

            return response()->json([
                'message' => 'User registered successfully !',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'role' => $user->role,
                    'statut' => $user->statut,
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
            $user = User::find($id);

            return response()->json([
                'status' => 200,
                'message' => 'Utilisateur trouvé avec succès !',
                'results' => [
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'telephone' => $user->telephone,
                    'email' => $user->email,
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

    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->photo) {
                Storage::delete($user->photo);
            }

            $photoName = time() . '.' . $request->photo->extension();
            $photoPath = $request->file('photo')->storeAs('photos', $photoName, 'public');
            $user['photo'] = $photoPath;

            // Mettre à jour l'utilisateur avec le chemin de la nouvelle photo
            $user->photo = $photoPath;
            $user->save();
        }

        return response()->json(['message' => 'Profile photo updated successfully.', 'profile_photo' => $photoPath]);
    }

//    public function store(LoginRequest $request)
//    {
//        try {
//            $credentials = $request->validated();
//
//            if (Auth::attempt($credentials)) {
//                $user = Auth::user();
//
//                // Create an API token for the user
//                $token = $user->createToken('token-name', ['*'])->plainTextToken;
//
//                // Return JSON response
//                return response()->json([
//                    'message' => 'You are logged in.',
//                    'user' => $user,
//                    'token' => $token,
//                    'tokenExpiry' => now()->addMinutes(60)->format('Y-m-d H:i:s'),
//                ]);
//            }
//
//            // Authentication failed
//            return response()->json([
//                'message' => 'Credentials do not match our records.',
//            ], 401);
//        } catch (\Exception $e) {
//            // Handle exceptions here
//            return response()->json([
//                'message' => 'Error during login: ' . $e->getMessage(),
//            ], 500);
//        }
//    }
//
//    public function changePassword(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'current_password' => 'required',
//            'password' => ['required', 'confirmed', Password::defaults()],
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()], 422);
//        }
//
//        $user = Auth::user();
//
//        if (!Hash::check($request->current_password, $user->password)) {
//            return response()->json(['message' => 'Le mot de passe actuel est incorrect'], 401);
//        }
//
//        $user->update([
//            'password' => Hash::make($request->password),
//        ]);
//
//        return response()->json(['message' => 'Mot de passe changé avec succès']);
//    }


}
