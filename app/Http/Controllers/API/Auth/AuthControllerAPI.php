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
            'phone' => 'required|digits:9',
            'student_number' => 'nullable|string|max:20',
        ], [
            'name.required'     => 'O nome é obrigatório.',
            'email.required'    => 'O email é obrigatório.',
            'email.email'       => 'Introduz um email válido.',
            'email.unique'      => 'Este email já se encontra registado.',
            'password.required' => 'A password é obrigatória.',
            'password.min'      => 'A password deve ter pelo menos 6 caracteres.',
            'phone.required'    => 'O telemóvel é obrigatório.',
            'phone.digits'      => 'O telemóvel deve ter 9 dígitos.',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone, 
            'n_aluno' => $request->student_number,
            'user_type_id' => 4, 
            'user_status_id' => 1, 
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
                'user' => $user,
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
