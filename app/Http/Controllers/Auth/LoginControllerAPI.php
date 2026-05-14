<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginControllerAPI extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->toArray(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json(['erro', 'Erro ao tentar autenticar'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (Hash::check($request->input('password'), $user->password)) {
            $user->generateToken();
            return $user;
        } else {
            return response()->json(['erro' => "Utilizador não encontrado"], 400);
        }
    }

    public function logout(Request $request)
    {
        $user = User::where('api_token', $request->header('Authorization'))->first();

        if ($user) {
            $user->api_token = null;
            $user->save();
        } else {
            return response()->json(['data' => 'Utilizador não encontrado'], 404);
        }

        return response()->json(['data' => 'Utilizador saiu'], 200);
    }
}
