<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);
    
        $credentials = $request->only('login', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
    
            // Génération d'un jeton d'authentification personnalisé avec expiration
            $token = $user->createToken('api_token', ['expires_in' => 30 * 60])->plainTextToken;
    
            return response()->json(['token' => $token]);
        }
    
        return response()->json(['error' => 'Les informations d\'identification sont incorrectes.'], 401);
    }
    

    public function store(Request $request)
    {
    
        $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'date_naissance' => 'required|date',
            'login' => 'required',
            'password' => 'required|min:6',
        ]);
    
        // Vérification du login
        if (User::where('login', $request->login)->exists()) {
            return response()->json(['error' => 'Le login existe déjà.'], 422);
        }
    
   
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'date_naissance' => $request->date_naissance,
            'login' => $request->login,
            'password' => Hash::make($request->password),
        ]);
    
        return response()->json($user, 201);
    }
    

  

    public function update(Request $request, User $user)
    {
        // Vérifiez si l'utilisateur existe
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable. La modification n\'est pas possible.'], 404);
        }

        $request->validate([
            'nom' => 'required|unique:users,nom,' . $user->id,
            'prenom' => 'required',
            'date_naissance' => 'required|date',
        ]);

        $data = [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'date_naissance' => $request->date_naissance,
        ];

        // Vérifiez si les champs login et password sont présents dans la demande
        if ($request->has('login')) {
            $data['login'] = $request->login;
        }

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user, 200);
    }

    public function index()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'Aucun utilisateur disponible.'], 404);
        }

        return response()->json($users, 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable.'], 404);
        }

        return response()->json($user);
    }

}
