<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\LiaSpace;
use App\Models\SpaceReserve;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LiaSpaceController extends Controller
{
    public function index(){
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 3) {
            return view('user.lia_space.index');
        }
        return redirect('/');
    }

    public function callendar(){
        if (Auth::user()->user_type_id == 3) {
            return view('user.lia_space.callendar');
        }
        return redirect('/');
    }

    // public function getPostos()
    // {
    //     return LiaSpace::all(['id', 'description', 'space_code']);
    // }

    public function getPostos()
    {
        return SpaceReserve::all(['id', 'description', 'start_date', 'end_date', 'space_code']);
    }

    public function getReservas(Request $request)
    {
        $postoId = $request->input('id');
        
        $query = SpaceReserve::with('users');

        if ($postoId) {
            $query->where('space_code', $postoId);
        }

        $reservas = $query->get();

        function generateColorFromId($id) {
            // Usa uma hash MD5 para gerar valores únicos
            $hash = md5($id);
        
            // Extrai os primeiros 6 caracteres para criar uma cor hexadecimal
            $color = '#' . substr($hash, 0, 6);
        
            // Assegura que a cor não é muito clara (verifica luminosidade)
            while (isColorTooLight($color)) {
                $hash = md5($hash); // Re-hash para gerar nova cor
                $color = '#' . substr($hash, 0, 6);
            }
        
            return $color;
        }
        
        function isColorTooLight($color) {
            // Converte HEX para RGB
            $r = hexdec(substr($color, 1, 2));
            $g = hexdec(substr($color, 3, 2));
            $b = hexdec(substr($color, 5, 2));
        
            // Calcula a luminosidade
            $luminosity = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
            // Considera claro se a luminosidade for maior que 0.8
            return $luminosity > 0.7;
        }
        
        // Busca o bolseiro a partir do occupant_id
        function getBolseiro($occupantId) {
            return DB::table('users')
                ->where('id', $occupantId)
                ->select('name as bolseiro', 'email as email')
                ->first(); 
        }

        // Transforma os dados para o formato necessário para o calendário
        return $reservas->map(function ($reserva) {
            $occupantId = $reserva->occupant_id;

            // Gera a cor com base no occupant_id
            $color = generateColorFromId($occupantId);
        
            // Busca o bolseiro com base no occupantId
            $bolseiro = getBolseiro($occupantId);

            return [
                'id' => $reserva->id,
                'title' => $reserva->description ?: "Posto {$reserva->space_code}",
                'start' => $reserva->start_date,
                'end' => $reserva->end_date,
                'bolseiro' => $bolseiro ? $bolseiro->bolseiro : 'Desconhecido',
                'email' => $bolseiro->email,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'users' => $reserva->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'color' => $user->color,
                    ];
                }),
            ];
        });        
    }

    public function getBolseiro(Request $request){
        $today = \Carbon\Carbon::now();
        $space = LiaSpace::where('space_code', $request->spaceID)->first();

        // if (!$space) {
        //     return response()->json(['error' => 'Espaço não encontrado.'], 404);
        // }

        $reservasComBolseiros = DB::table('space_reserves as sr')
            ->join('lia_spaces as ls', 'ls.id', '=', 'sr.id') 
            ->join('users as u', 'u.id', '=', 'sr.occupant_id')  // Junção com a tabela users
            ->select(
                'sr.start_date as data_inicio',
                'sr.end_date as data_fim',
                'u.name as bolseiro'
            )
            ->where('ls.space_code', '=', $request->postoID)  // Filtro para o código do espaço
            ->where('sr.start_date', '<=', $today)  // Apenas reservas ativas ou já iniciadas
            ->where('sr.end_date', '>=', $today)  // Apenas reservas ainda válidas
            ->get();
                

        return response()->json(['bolseiro' => $reservasComBolseiros]);
    }

    public function reservas(){
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 3) {
            return view('admin.lia_space.reservas', ['reservas' => SpaceReserve::all(),'users'=>User::all()]);
        }
        return redirect('/');
    }

    public function getSpace(Request $request)
    {
        $space = LiaSpace::where('space_code', $request->spaceID)->first();

        if (!$space) {
            return response()->json(['space' => null]);
        }

        $today = \Carbon\Carbon::today();

        $reservas = DB::table('lia_space_space_reserve')
        ->join('space_reserves', 'lia_space_space_reserve.space_reserve_id', '=', 'space_reserves.id')
        ->where('lia_space_space_reserve.lia_space_id', $space->id)
            ->whereDate('space_reserves.end_date', '>=', $today)
            ->select('space_reserves.start_date', 'space_reserves.end_date')
            ->get();

        // Verifica se há reservas antes de formatá-las
        $reservasFormatted = [];
        if ($reservas->isNotEmpty()) {
            $reservasFormatted = $reservas->map(function ($reserva) {
                return [
                    'start_date' => \Carbon\Carbon::parse($reserva->start_date)->format('d/m/Y'),
                    'end_date' => \Carbon\Carbon::parse($reserva->end_date)->format('d/m/Y')
                ];
            });
        }

        return response()->json(['space' => $space, 'itens' => $space->itens, 'reservas' => $reservasFormatted]);
    }

    public function checkAvailability(Request $request){
        if($request->spaceID == null){
            return response()->json(['message' => 'Nenhum espaço foi selecionado'], 400);
        }
        if(LiaSpace::where('space_code', $request->spaceID)->first() == null){
            return response()->json(['message' => 'Espaço inativo'], 400);
        }
        if($request->start_date == "" || $request->end_date == ""){
            return response()->json(['message' =>'Tem de preencher ambas as datas para verificar a disponibilidade'], 400);
        }
        
        $space = LiaSpace::where('space_code', $request->spaceID)->first();

        return $space == null ? 
            response()->json(['message' => 'Espaço Inativo'], 400) 
            : 
            ($this->available($space, $request) ? response()->json(['available' => true]) : response()->json(['available' => false]));
    }

    private function available($space, $request){
        return collect($space->spaceReserve)->isEmpty() ? true : $this->checkReserveDates($space, $request->start_date, $request->end_date);
    }

    private function checkReserveDates($space, $start_date, $end_date){
        $reserves = $space->spaceReserve()->where(function($query) use ($start_date, $end_date){
                                    $query->whereBetween('start_date', [$start_date, $end_date])
                                        ->orWhereBetween('end_date', [$start_date, $end_date]);
                                })
                                ->orWhere(function($reserve) use ($start_date, $end_date){
                                    $reserve->where('start_date', '<', $start_date)
                                        ->where('end_date', '>', $end_date);
                                })
                                ->get();
        return collect($reserves)->isEmpty() ? true : false;
    }

    public function reserve(Request $request){
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 3) {
            return view('user.lia_space.reserve', [
                'space' => LiaSpace::where('space_code', $request->spaceID)->first(),
                'costCenters' => CostCenter::all(),
                'users' => User::where('user_type_id', 5)->get(),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);
        }
        return redirect('/');
    }

    public function createReserve($id, Request $request){

        $request->validate([
            'description' => 'required'
        ]);

        $user = $request->exists('occupant_email') ? $this->newUser($request) : User::find($request->occupant_id);

        $costCenter = CostCenter::find($request->cost_center_id);
        
        $space = LiaSpace::find($id);

        $reserve = SpaceReserve::create([
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'cost' => $space->cost,
            'occupant_id' => $user->id,
            'user_id' => $user->id
        ]);

        $reserve->liaSpace()->attach($space->id);
        $reserve->users()->attach(Auth::id());

        $costCenter->total_cost = $costCenter->total_cost + $space->cost;
        $costCenter->total_debt = $costCenter->total_debt + $space->cost;
        $costCenter->save();



        return redirect('/lia-space')->with('success', 'Reserva realizada!');
    }

    private function newUser($request){
        $request->validate([
            'occupant_email' => 'required'
        ]);

        $user = User::create([
            'email' => $request->occupant_email,
            'password' => Hash::make('12345'),
            'user_type_id' => '5',
            'user_status_id' => '1'
        ]);

        return $user;
    }

    public function edit($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            $space = LiaSpace::where('space_code', $id)->first();
            if ($space == null) {
                return response()->json(['space' => $space]);
            }

            return view('user.lia_space.edit', [
                'space' => $space,
                'itens' => $space->itens
            ]);
        }
        return redirect('/');
    }
}