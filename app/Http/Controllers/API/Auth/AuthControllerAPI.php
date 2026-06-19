<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthControllerAPI extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if(User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email já está em uso.'
            ], 409); // 409 = Conflict
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => 4, // 4 = Tipo de utilizador "Cliente" (pode ser alterado conforme a necessidade)
            'user_status_id' => 1, // 1 = Ativo (pode ser alterado conforme a necessidade)
            'image' => 'images/empty.png'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Conta criada com sucesso',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        // 1. Valida os dados de entrada
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 2. Tenta autenticar (O equivalente ao bcrypt.compare no Node.js)
        if (Auth::attempt($request->only('email', 'password'))) {
            
            $user = User::where('email', $request->email)->first();
            
            // Apaga tokens antigos (opcional, para garantir que só tem 1 telemóvel ligado de cada vez)
            $user->tokens()->delete();
            
            // Gera um novo Token
            $token = $user->createToken('LIA_Mobile_App')->plainTextToken;

            // 3. Devolve os dados e o Token
            return response()->json([
                'status' => 'success',
                'message' => 'Login efetuado com sucesso.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type_id' => $user->user_type_id
                ]
            ], 200); // 200 = OK
        }

        // Se falhar a password ou email
        return response()->json([
            'status' => 'error',
            'message' => 'Email ou password incorretos.'
        ], 401); // 401 = Unauthorized
    }

    public function logout(Request $request)
    {
        // Apaga o token que o telemóvel usou para fazer este pedido
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sessão terminada com sucesso.'
        ], 200);
    }
    
}
