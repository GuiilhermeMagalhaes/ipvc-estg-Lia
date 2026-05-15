<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Models\Reserve;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(){
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.users.index', ['users' => User::all()]);
        }
        return redirect('/');
    }

    public function show($id){
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.users.show', ['user' => User::find($id), 'reserves'=>Reserve::all()]);
        }
        return redirect('/');
    }

    public function edit($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.users.edit', [
                'user' => User::find($id),
                'user_types' => UserType::all()
            ]);
        }
        return redirect('/');
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $n_gestores = User::where('user_type_id', '=', 1)->count();

        if($n_gestores==1 && $user->user_type_id == 1){
            return redirect(route('user.edit', $user->id))->with('toast_error', 'É necessário existir pelo menos 1 gestor!');
        }else{
            $user->update(['user_type_id' => $request->user_type_id]);
            $user->save();
            return redirect(route('user.show', $user->id));
        }
    }
}
