<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ciclica;
use App\Models\CostCenter;
use App\Models\Kit;
use App\Models\Item;
use App\Models\KitReserve;
use App\Models\ItemReserve;
use App\Models\Reserve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\PedidoRequisicao;
use Illuminate\Support\Facades\Notification;

class ReserveController extends Controller
{
    public function index()
    {
        if(Auth::user()->user_type_id == 6){
            return redirect()->to('/perfil')->with('toast_error', 'Atualize o seu perfíl antes de criar uma reserva!');
        }
        return view('user.reserve.create', ['ciclica' => Ciclica::all()], ['costCenters' => CostCenter::all()]);
    }

    public function create(Request $request)
    {
        if ($request->start_date > $request->end_date) {
            return redirect()->to('/reserve')->with('toast_error', 'As datas escolhidas não são válidas!');
        }

        $request->validate([
            'description' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ], [
            'description.required' => 'Necessita de uma razão para efetuar a reserva',
            'start_date.required' => 'Data de inicio da reserva é necessaria',
            'end_date.required' => 'Data de fim da reserva é necessaria'
        ]);

        $reserve = [
            "user_id" => Auth::id(),
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
            "description" => $request->description,
            "cost_center_id" => $request->cost_center_id,
            "ciclica_id" => $request->ciclica_id,
            "cost" => 0,
            "kits" => [],
            "delivery_date" => null,
            "return_date" => null
        ];

        session()->put('reserve', $reserve);

        return redirect()->to('/reserve/info')->with('toast_success', 'Reserva iniciada!');
    }

    public function reserveInfo()
    {
        return view('user.reserve.info');
    }

    public function addItem(Request $request, $id)
    {
        $item = Item::find($id);
        
        if (!session()->has('reserve')) {
            return redirect()->route('reserve.index')->with('warning', 'Deve iniciar uma reserva para poder adicionar itens!');
        }

        $quantidade = $request->input('quantity', 1);
        $existingItems = session()->get('reserve.itens', []);

        // 1. Verifica quantas unidades físicas existem no total
        $totalUnidadesFisicas = \App\Models\ItemUnity::where('item_id', $item->id)
                                ->where('item_unity_state_id', 1)
                                ->count();

        // 2. Verifica quantos itens iguais a este já estão no carrinho
        $reservedCount = count(array_filter($existingItems, function($i) use ($item) {
            return $i->id == $item->id;
        }));

        // 3. Verifica se há stock suficiente no laboratório
        if (($reservedCount + $quantidade) > $totalUnidadesFisicas) {
            return back()->with('warning', 'A quantidade desejada excede o nosso stock físico disponível!');
        }

        // 4. Adiciona o item à reserva as vezes que o utilizador pediu
        for ($i = 0; $i < $quantidade; $i++) {
            session()->push('reserve.itens', $item);
        }

        return back()->with('toast_success', 'Item adicionado à reserva!');
    }

    public function addKit(Request $request, $id)
    {
        $kit = Kit::find($id);

        if (!session()->has('reserve')) {
            return redirect()->route('reserve.index')->with('warning', 'Deve iniciar uma reserva para poder adicionar kits!');
        }

        $quantidade = $request->input('quantity', 1);
        $existingKits = session()->get('reserve.kits', []);

        // Verifica quantos kits iguais a este já estão no carrinho
        $reservedCount = count(array_filter($existingKits, function($k) use ($kit) {
            return $k->id == $kit->id;
        }));

        // Quando acabar a tabela kit_unity, substituímos este "10" 
        // pela contagem real de KitUnity (tal como fizemos nos Itens). 
        // Por agora, assumimos que há stock para não bloquear testes.
        $totalUnidadesKitsFisicos = 10; 

        if (($reservedCount + $quantidade) > $totalUnidadesKitsFisicos) {
            return back()->with('warning', 'Quantidade desejada indisponível em stock!');
        }

        // Adiciona à reserva
        for ($i = 0; $i < $quantidade; $i++) {
            session()->push('reserve.kits', $kit);
        }

        return back()->with('toast_success', 'Kit adicionado à reserva!');
    }

    public function removeKit($id)
    {
        // 1. Vai buscar os kits (como uma Collection do Laravel)
        $kits = collect(session()->get('reserve.kits', []));

        // 2. Filtra a coleção: rejeita o kit que tem o ID que queremos remover
        $kitsAtualizados = $kits->reject(function ($kit) use ($id) {
            return $kit->id == $id;
        })->values()->all(); // values() garante que os índices (0,1,2) são reordenados

        // 3. Grava o novo array limpo na sessão
        session()->put('reserve.kits', $kitsAtualizados);

        return back()->with('toast_success', 'Kit removido!');
    }

    public function removeItem($id)
    {
        // A mesma exata lógica para os itens
        $itens = collect(session()->get('reserve.itens', []));

        $itensAtualizados = $itens->reject(function ($item) use ($id) {
            return $item->id == $id;
        })->values()->all();

        session()->put('reserve.itens', $itensAtualizados);

        return back()->with('toast_success', 'Item removido!');
    }

    public function cancelReserve()
    {
        session()->pull('reserve');

        return redirect('/reserve')->with('success', 'Reserva foi cancelada!');
    }

    public function confirmReserve()
    {
        if (empty(session()->get('reserve.kits')) && empty(session()->get('reserve.itens'))) {
            return back()->with('warning', 'Adicione kits e/ou itens à reserva para poder concluir!');
        }

        $reserve = Reserve::create([
            'description' => session()->get('reserve.description'),
            'cost_center_id' => session()->get('reserve.cost_center_id'),
            'ciclica_id' => session()->get('reserve.ciclica_id'),
            'user_id' => session()->get('reserve.user_id'),
            'start_date' => session()->get('reserve.start_date'),
            'end_date' => session()->get('reserve.end_date'),
            'cost' => 0,
            'reserve_state_id' => 1,
            'delivery_date' => null,
            'return_date' => null  
        ]);

        if (session()->get('reserve.kits')) {
            foreach (session()->get('reserve.kits') as $kit) {
                KitReserve::create([
                    'reserve_id' => $reserve->id,
                    'kit_id' => $kit->id
                ]);
            }
        }

        if (session()->get('reserve.itens')) {
            foreach (session()->get('reserve.itens') as $item) {
                ItemReserve::create([
                    'reserve_id' => $reserve->id,
                    'item_id' => $item->id
                ]);
            }
        }

        $gestores = User::where('user_type_id', 1)->get(); 

        if($gestores->isNotEmpty()) {
            Notification::send($gestores, new PedidoRequisicao($reserve));
        }

        session()->forget('reserve');

        return redirect('/')->with('success', 'Reserva efetuada com sucesso!');
    }
}