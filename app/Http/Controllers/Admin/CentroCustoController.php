<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CostCenter;
use App\Models\CostCenterUser;
use App\Models\Reserve;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class CentroCustoController extends Controller
{
    public function index(){
        if (Auth::user()->user_type_id == 1) {
            return view('admin.centros.index', ['centros' => CostCenter::all(), 
                                            'cost_center_user' => CostCenterUser::all(),
                                            'users'=>User::all()]);
        }
        return redirect('/');
    }

    public function create()
    {
        if (Auth::user()->user_type_id == 1) {
            return view('admin.centros.create', ['users' => User::all()]);
        }
        return redirect('/');
    }
    
    public function orientadorCreate()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 3) {
            return view('orientador.create', ['users' => User::all()]);
        }
        return redirect('/');
    }

    public function store(Request $request)
    {   
        $request->validate(
            [
                'name' => 'required'
            ],
            [
                'name.required' => 'O Centro deve ter um nome',
            ]
        );

        CostCenter::create([
            'name' => $request->name,
            'total_cost' => 0,
            'total_debt' => 0
        ]);

        $centro = CostCenter::where(['name' => $request->name])->first();
        
        CostCenterUser::create([
            'cost_center_id' => $centro->id,
            'user_id' => $request->user_id
        ]);

        if(Auth::user()->user_type_id == 1){
            return redirect('admin/centros')->with('toast_success', 'Centro criado com sucesso!');
        }else{
            return redirect('/')->with('toast_success', 'Centro criado com sucesso!');
        }
    }

    public function pagar($id)
    {   
        $centro = CostCenter::find($id);
        
        $centro->total_debt = 0;
        $centro->save();

        return redirect('admin/centros')->with('toast_success', 'Dívidas pagas com sucesso!');
    }

    public function destroy($id)
    {
        if($id==1){
            return redirect('admin/centros')->with('toast_error', 'Não é possível eliminar este centro!');
        }
        
        $centro = CostCenter::find($id);
        $centroDefault = CostCenter::find(1);
        $costCenteruser = CostCenterUser::where(['cost_center_id' => $id]);
        $centroReservas = Reserve::where(['cost_center_id'=> $id])->get();


        if (!$centroReservas->isEmpty()) {
            foreach ($centroReservas as $reserva) {
                $reserva->cost_center_id = 1;
                $reserva->save();
            }
        }

        $centroDefault->total_cost = $centroDefault->total_cost + $centro->total_cost;
        $centroDefault->total_debt = $centroDefault->total_debt + $centro->total_debt;
        $centroDefault->save();


        if(isset($costCenteruser)){
            $costCenteruser -> delete();
        }

        $centro->delete();

        return redirect('admin/centros')->with('toast_success', 'Centro Eliminado');
    }

    public function reservas($id){
        if (Auth::user()->user_type_id == 1) {
            return view('admin.centros.reservas', ['reserves' => Reserve::all(), 'id' => $id]);
        }
        return redirect('/');
    }
}
