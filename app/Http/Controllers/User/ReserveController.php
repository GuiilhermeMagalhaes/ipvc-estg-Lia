<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ciclica;
use App\Models\CostCenter;
use App\Models\Kit;
use App\Models\Item;
use App\Models\KitReserve;
use App\Models\ItemReserve;
use App\Models\KitUnity;
use Carbon\Carbon;
use App\Models\Reserve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\PedidoRequisicao;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class ReserveController extends Controller
{
    public function index()
    {
        if(Auth::user()->user_type_id == 6){
            return redirect()->to('/perfil')->with('toast_error', 'Atualize o seu perfíl antes de criar uma reserva!');
        }
        return view('user.reserve.create', [
            'ciclica' => Ciclica::all(),
            'costCenters' => CostCenter::all()
        ]);
    }

    public function create(Request $request)
    {
        
        $request->validate([
            'description' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ], [
            'description.required' => 'Necessita de uma razão para efetuar a reserva',
            'start_date.required' => 'Data de inicio da reserva é necessaria',
            'end_date.required' => 'Data de fim da reserva é necessaria'
        ]);

        if ($request->start_date > $request->end_date) {
            return redirect()->to('/reserve')->with('toast_error', 'As datas escolhidas não são válidas!');
        }


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
    $kit = Kit::findOrFail($id);

    if (!session()->has('reserve')) {
        return redirect()->route('reserve.index')->with('warning', 'Deve iniciar uma reserva para poder adicionar kits!');
    }

    $quantidadeDesejada = (int) $request->input('quantity', 1);
    if ($quantidadeDesejada <= 0) return back()->with('warning', 'Quantidade inválida!');

    $startDate = Carbon::parse(session()->get('reserve.start_date'));
    $endDate = Carbon::parse(session()->get('reserve.end_date'));
    $sessionCiclicaId = (int) session()->get('reserve.ciclica_id', 1);
    
    $existingKits = session()->get('reserve.kits', []);
    $jaNoCarrinho = isset($existingKits[$kit->id]) ? $existingKits[$kit->id]['quantity'] : 0;
    $totalAAdicionar = $jaNoCarrinho + $quantidadeDesejada;

    // 1. GERAR CALENDÁRIO DO PEDIDO ATUAL
    $periodoDatas = [];
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
        if ($sessionCiclicaId === 1 || $date->dayOfWeek === ($sessionCiclicaId - 2)) {
            $periodoDatas[] = ['data' => $date->format('Y-m-d'), 'dayOfWeek' => $date->dayOfWeek];
        }
    }

    $totalFisico = KitUnity::where('kit_id', $kit->id)->where('kit_unity_state_id', 1)->count();

    // 2. BUSCAR RESERVAS QUE COINCIDEM
    $reservasOcupantes = DB::table('kit_reserve')
        ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
        ->where('kit_reserve.kit_id', $kit->id)
        ->whereIn('reserves.reserve_state_id', [1, 2, 7])
        ->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('reserves.start_date', [$startDate, $endDate])
              ->orWhereBetween('reserves.end_date', [$startDate, $endDate])
              ->orWhere(function ($sub) use ($startDate, $endDate) {
                  $sub->where('reserves.start_date', '<=', $startDate)
                      ->where('reserves.end_date', '>=', $endDate);
              });
        })
        ->select('reserves.start_date', 'reserves.end_date', 'kit_reserve.quantity', 'reserves.ciclica_id')
        ->get();

    // 3. RESTRINÇÃO DE STOCK CÍCLICO
    $minimoDisponivelNoPeriodo = $totalFisico;

    foreach ($periodoDatas as $pData) {
        $dia = $pData['data'];
        $diaSemanaAtual = $pData['dayOfWeek'];
        $ocupadosHoje = 0;

        foreach ($reservasOcupantes as $reserva) {
            if ($dia >= $reserva->start_date && $dia <= $reserva->end_date) {
                if ((int)$reserva->ciclica_id === 1) {
                    $ocupadosHoje += $reserva->quantity;
                } else {
                    $diaSemanaReservaAntiga = (int)$reserva->ciclica_id - 2;
                    if ($diaSemanaAtual === $diaSemanaReservaAntiga) {
                        $ocupadosHoje += $reserva->quantity;
                    }
                }
            }
        }

        $disponivelHoje = $totalFisico - $ocupadosHoje;
        if ($disponivelHoje < $minimoDisponivelNoPeriodo) {
            $minimoDisponivelNoPeriodo = $disponivelHoje;
        }
    }

    if ($totalAAdicionar > $minimoDisponivelNoPeriodo) {
        return back()->with('warning', 'Quantidade indisponível! Máximo livre neste período: ' . $minimoDisponivelNoPeriodo);
    }

    $existingKits[$kit->id] = [
        'id' => $kit->id,
        'name' => $kit->name,
        'price' => $kit->price,
        'quantity' => $totalAAdicionar
    ];

    session()->put('reserve.kits', $existingKits);
    return back()->with('toast_success', 'Kit adicionado!');
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
    // Verifica se existe alguma reserva iniciada na sessão antes de tentar cancelar
    if (session()->has('reserve')) {
        session()->forget('reserve');
    }

    return redirect('/reserve')->with('success', 'A reserva foi cancelada com sucesso!');
}


    public function confirmReserve()
{
    // PASSO 1: Verificação de Segurança
    if (empty(session()->get('reserve.kits'))) {
        return back()->with('warning', 'Adicione kits à reserva para poder concluir!');
    }

    // PASSO 2: Início da Transação de Segurança
    DB::beginTransaction();

    try {
        // PASSO 3: Criação da Reserva Principal
        $reserve = Reserve::create([
            'description'      => session()->get('reserve.description'),
            'cost_center_id'   => session()->get('reserve.cost_center_id'),
            'ciclica_id'       => session()->get('reserve.ciclica_id'),
            'user_id'          => session()->get('reserve.user_id'),
            'start_date'       => session()->get('reserve.start_date'),
            'end_date'         => session()->get('reserve.end_date'),
            'cost'             => 0,
            'reserve_state_id' => 1, 
            'delivery_date'    => null,
            'return_date'      => null  
        ]);

        // PASSO 4: Gravação dos Kits com as Quantidades Reais
        foreach (session()->get('reserve.kits') as $kitId => $kitData) {
            KitReserve::create([
                'reserve_id' => $reserve->id,
                'kit_id'     => $kitId, 
                'quantity'   => $kitData['quantity'] 
            ]);
        }

        // PASSO 5: Finalização com Sucesso (Commit)
        DB::commit();

    } catch (\Exception $e) {
        // PASSO 6: Cancelamento em caso de Falha (Rollback)
        DB::rollBack();
        return back()->with('toast_error', 'Ocorreu um erro ao processar a reserva. Tente novamente.');
    }

    // PASSO 7: Notificação dos Gestores
    $gestores = User::where('user_type_id', 1)->get(); 
    if ($gestores->isNotEmpty()) {
        Notification::send($gestores, new PedidoRequisicao($reserve));
    }

    // PASSO 8: Limpeza de Memória
    session()->forget('reserve');

    return redirect('/')->with('success', 'Reserva efetuada com sucesso!');
}
}