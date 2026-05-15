<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\LiaSpace;
use App\Models\SpaceReserve;
use App\Models\Item;
use App\Models\UserType;
use App\Models\Reserve;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\KitReserve;
use App\Models\Kit;
use App\Models\ItemReserve;


class PerfilController extends Controller
{
    public function index()
    {
        return view('user.perfil.index', ['userTypes' => UserType::all()]);
    }

    public function reserves()
    {

        $reservas = Reserve::all();

        foreach ($reservas as $reserve) {
            if ($reserve->reserve_state_id == 7) {
                $todaydate = date('Y-m-d');
                if ($todaydate > $reserve->end_date) {
                    $reserve->reserve_state_id = 4;
                }
                $reserve->save();
            }
        }

        return view('user.perfil.reserves', [
            'reserves' => $reservas,
            'reserve_kits' => KitReserve::all(),
            'kits' => Kit::all(),
            'reserve_itens' => ItemReserve::all(),
            'itens' => Item::all()
        ]);
    }

    public function edit()
    {
        return view('user.perfil.edit', ['userTypes' => UserType::all()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->user_type_id == 6) {
            //Se for um aluno
            if ($request->tipo_user == 1) {
                $request->validate(
                    [
                        'name' => 'required',
                        'curso' => 'required',
                        'phone' => 'required|unique:users,phone->ignore($user->phone)|digits_between:1,9',
                        'n_aluno' => 'required|unique:users,phone->ignore($user->n_aluno)'
                    ],
                    [
                        'name.required' => 'O utilizador deve ter um nome!',
                        'phone.unique' => 'Já existe uma conta com este número de telemóvel!',
                        'phone.digits_between' => 'O número de telemóvel deve ter no máximo 9 dígitos!',
                        'curso.required' => 'Um aluno deve pertencer a um curso!',
                        'n_aluno.required' => 'Um aluno deve ter um número!',
                        'n_aluno.unique' => 'O número de aluno deve ser único!'
                    ]
                );

                if ($request->image != null) {
                    $imagePath = $request->file('image');
                    $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
                    $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
                } else {
                    $path = $user->image;
                }

                $user->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'n_aluno' => $request->n_aluno,
                    'curso' => $request->curso,
                    'tipo_user' => $request->tipo_user,
                    'user_type_id' => 4,
                    'image' => $path
                ]);

                $user->save();

                return redirect(route('perfil.index'));
            }

            $request->validate(
                [
                    'name' => 'required',
                    'phone' => 'required|unique:users,phone->ignore($user->phone)|digits_between:1,9'
                ],
                [
                    'name.required' => 'O utilizador deve ter um nome!',
                    'phone.unique' => 'Já existe uma conta com este número de telemóvel!',
                    'phone.digits_between' => 'O número de telemóvel deve ter no máximo 9 dígitos!'
                ]
            );
    
            if ($request->image != null) {
                $imagePath = $request->file('image');
                $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
                $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
            } else {
                $path = $user->image;
            }
    
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'n_aluno' => $request->n_aluno,
                'curso' => $request->curso,
                'tipo_user' => $request->tipo_user,
                'user_type_id' => 4,
                'image' => $path
            ]);
    
            $user->save();
    
            return redirect(route('perfil.index'));
        } else {
            //Se for um aluno
            if ($request->tipo_user == 1) {
                $request->validate(
                    [
                        'name' => 'required',
                        'curso' => 'required',
                        'phone' => 'required|unique:users,phone->ignore($user->phone)|digits_between:1,9',
                        'n_aluno' => 'required|unique:users,phone->ignore($user->n_aluno)'
                    ],
                    [
                        'name.required' => 'O utilizador deve ter um nome!',
                        'phone.unique' => 'Já existe uma conta com este número de telemóvel!',
                        'phone.digits_between' => 'O número de telemóvel deve ter no máximo 9 dígitos!',
                        'curso.required' => 'Um aluno deve pertencer a um curso!',
                        'n_aluno.required' => 'Um aluno deve ter um número!',
                        'n_aluno.unique' => 'O número de aluno deve ser único!'
                    ]
                );

                if ($request->image != null) {
                    $imagePath = $request->file('image');
                    $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
                    $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
                } else {
                    $path = $user->image;
                }

                $user->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'n_aluno' => $request->n_aluno,
                    'curso' => $request->curso,
                    'tipo_user' => $request->tipo_user,
                    'user_type_id' => $user->user_type_id,
                    'image' => $path
                ]);

                $user->save();

                return redirect(route('perfil.index'));
            }

            $request->validate(
                [
                    'name' => 'required',
                    'phone' => 'required|unique:users,phone->ignore($user->phone)|digits_between:1,9'
                ],
                [
                    'name.required' => 'O utilizador deve ter um nome!',
                    'phone.unique' => 'Já existe uma conta com este número de telemóvel!',
                    'phone.digits_between' => 'O número de telemóvel deve ter no máximo 9 dígitos!'
                ]
            );
    
            if ($request->image != null) {
                $imagePath = $request->file('image');
                $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
                $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
            } else {
                $path = $user->image;
            }
    
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'n_aluno' => $request->n_aluno,
                'curso' => $request->curso,
                'tipo_user' => $request->tipo_user,
                'user_type_id' => $user->user_type_id,
                'image' => $path
            ]);
    
            $user->save();
    
            return redirect(route('perfil.index'));
        }
    }
}
