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

        $ciclicaId = (int) $request->input('ciclica_id', 1); // Se não enviar, assume 1 (Normal)

        if ($ciclicaId > 1) {
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $periodoDatas = [];

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                if ($date->dayOfWeek === ($ciclicaId - 2)) {
                    $periodoDatas[] = $date->format('Y-m-d');
                }
            }

            if (empty($periodoDatas)) {
                return redirect()->back()->withInput()->with('toast_error', 'O dia de semana selecionado não existe no período escolhido!');
            }
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
    $item = Item::findOrFail($id);


        if (is_null($item->price_day)) {
            return back()->with('toast_error', "O item '{$item->nome}' não tem preço diário definido. Contacte o administrador.");
        }

    if (!session()->has('reserve')) {
        return redirect()->route('reserve.index')->with('warning', 'Deve iniciar uma reserva para poder adicionar itens!');
    }

    $quantidadeDesejada = (int) $request->input('quantity', 1);
    if ($quantidadeDesejada <= 0) return back()->with('warning', 'Quantidade inválida!');

    $startDate = Carbon::parse(session()->get('reserve.start_date'));
    $endDate = Carbon::parse(session()->get('reserve.end_date'));
    $sessionCiclicaId = (int) session()->get('reserve.ciclica_id', 1);
    
    
    $existingItems = session()->get('reserve.items', []);
    $jaNoCarrinho = isset($existingItems[$item->id]) ? $existingItems[$item->id]['quantity'] : 0;
    $totalAAdicionar = $jaNoCarrinho + $quantidadeDesejada;

    
    $periodoDatas = [];
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
        if ($sessionCiclicaId === 1 || $date->dayOfWeek === ($sessionCiclicaId - 2)) {
            $periodoDatas[] = ['data' => $date->format('Y-m-d'), 'dayOfWeek' => $date->dayOfWeek];
        }
    }

    
    $totalFisico = DB::table('item_unity')
        ->where('item_id', $item->id)
        ->where('item_unity_state_id', 1)
        ->count();

    
    $reservasOcupantes = DB::table('item_reserve')
        ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
        ->where('item_reserve.item_id', $item->id)
        ->whereIn('reserves.reserve_state_id', [1, 2, 4, 7])
        ->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('reserves.start_date', [$startDate, $endDate])
              ->orWhereBetween('reserves.end_date', [$startDate, $endDate])
              ->orWhere(function ($sub) use ($startDate, $endDate) {
                  $sub->where('reserves.start_date', '<=', $startDate)
                      ->where('reserves.end_date', '>=', $endDate);
              });
        })
        ->select('reserves.start_date', 'reserves.end_date', 'item_reserve.quantity', 'reserves.ciclica_id')
        ->get();

    
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

    
    $existingItems[$item->id] = [
        'id'       => $item->id,
        'name'     => $item->nome,
        'price'    => $item->price_day,
        'quantity' => $totalAAdicionar
    ];

    session()->put('reserve.items', $existingItems);
    return back()->with('toast_success', 'Item adicionado!');
}



  public function addKit(Request $request, $id)
{
    $kit = Kit::findOrFail($id);


        if (is_null($kit->price_day)) {
            return back()->with('toast_error', "O kit '{$kit->name}' não tem preço diário definido. Contacte o administrador.");
        }

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

    
    $periodoDatas = [];
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
        if ($sessionCiclicaId === 1 || $date->dayOfWeek === ($sessionCiclicaId - 2)) {
            $periodoDatas[] = ['data' => $date->format('Y-m-d'), 'dayOfWeek' => $date->dayOfWeek];
        }
    }

    $totalFisico = KitUnity::where('kit_id', $kit->id)->where('kit_unity_state_id', 1)->count();

    
    $reservasOcupantes = DB::table('kit_reserve')
        ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
        ->where('kit_reserve.kit_id', $kit->id)
        ->whereIn('reserves.reserve_state_id', [1, 2, 4, 7])
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
        'price_day' => $kit->price_day,
        'quantity' => $totalAAdicionar
    ];

    session()->put('reserve.kits', $existingKits);
    return back()->with('toast_success', 'Kit adicionado!');
}






   /* public function removeKit($id)
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

*/



    public function removeKit($id)
{
    
    $kits = session()->get('reserve.kits', []);

    
    if (isset($kits[$id])) {
        unset($kits[$id]);
        session()->put('reserve.kits', $kits);
    }

    return back()->with('toast_success', 'Kit removido!');
}

public function removeItem($id)
{
    
    $items = session()->get('reserve.items', []);

    
    if (isset($items[$id])) {
        unset($items[$id]);
        session()->put('reserve.items', $items);
    }

    return back()->with('toast_success', 'Item removido!');
}




    public function cancelReserve()
{
    if (session()->has('reserve')) {
        session()->forget('reserve');
    }

    return redirect('/reserve')->with('success', 'A reserva foi cancelada com sucesso!');
}


    public function confirmReserve()
{
    $kits = session()->get('reserve.kits', []);
    $items = session()->get('reserve.items', []);

    // --- ALTERAÇÃO 2: Verificação de Segurança (Bloqueia se AMBOS estiverem vazios) ---
    if (empty($kits) && empty($items)) {
        return back()->with('warning', 'Adicione pelo menos um kit ou um item à reserva para poder concluir!');
    }


    $startDate = Carbon::parse(session()->get('reserve.start_date'));
    $endDate = Carbon::parse(session()->get('reserve.end_date'));
    $ciclicaId = session()->get('reserve.ciclica_id', 1);
    $numero_dias = 0;

    // Lógica de contagem de dias (igual à que tens no admin)
    if ($ciclicaId == 1 || $ciclicaId == null) {
        $numero_dias = $startDate->diffInDays($endDate) + 1;
        if ($numero_dias == 0) $numero_dias = 1;
    } else {
        $diaSemanaAlvo = $ciclicaId - 2;
        $numero_dias = $startDate->diffInDaysFiltered(function (Carbon $date) use ($diaSemanaAlvo) {
            return $date->dayOfWeek === $diaSemanaAlvo;
        }, $endDate);

        if ($endDate->dayOfWeek === $diaSemanaAlvo) {
            $numero_dias++;
        }
        if ($numero_dias == 0) {
            return back()->with('toast_error', 'O dia de semana cíclico selecionado não ocorre nenhuma vez no período de datas configurado.');
        }
    }

    $custo_estimado = 0;

    foreach ($items as $itemData) {
        if (is_null($itemData['price'])) {
            return back()->with('toast_error', "O item '{$itemData['name']}' não tem um preço válido. Remova-o ou contacte o administrador.");
        }
        $custo_estimado += ($itemData['price'] * $numero_dias * $itemData['quantity']);
    }
    

    // Somar preço dos kits do carrinho com validação de nulos
    foreach ($kits as $kitData) {
        if (is_null($kitData['price_day'])) {
            return back()->with('toast_error', "O kit '{$kitData['name']}' não tem um preço válido. Remova-o ou contacte o administrador.");
        }
        $custo_estimado += ($kitData['price_day'] * $numero_dias * $kitData['quantity']);
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
            'estimated_cost'   => $custo_estimado,
            'reserve_state_id' => 1, 
            'delivery_date'    => null,
            'return_date'      => null  
        ]);

        

        if (!empty($kits)) {
            foreach ($kits as $kitId => $kitData) {
                KitReserve::create([
                    'reserve_id' => $reserve->id,
                    'kit_id'     => $kitId, 
                    'quantity'   => $kitData['quantity'] 
                ]);
            }
        }

        if (!empty($items)) {
            foreach ($items as $itemId => $itemData) {
                ItemReserve::create([
                    'reserve_id' => $reserve->id,
                    'item_id'    => $itemId, 
                    'quantity'   => $itemData['quantity'] 
                ]);
            }
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

public function cancelarMinhaReserva($id)
    {
        $reserve = \App\Models\Reserve::findOrFail($id);

        // Só o dono da reserva a pode cancelar
        if ($reserve->user_id != \Illuminate\Support\Facades\Auth::id()) {
            return back()->with('toast_error', 'Não tem permissão para cancelar esta reserva.');
        }

        // Só é possível cancelar enquanto está pendente (ainda não foi processada)
        if ($reserve->reserve_state_id != 1) {
            return back()->with('toast_error', 'Só pode cancelar reservas que ainda estão pendentes.');
        }

        $reserve->reserve_state_id = 10; // Cancelada
        $reserve->save();

        return back()->with('toast_success', 'Reserva cancelada com sucesso!');
    }
}