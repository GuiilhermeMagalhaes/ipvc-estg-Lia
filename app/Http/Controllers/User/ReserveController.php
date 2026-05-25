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

    public function addItem(Request $request,$id)
    {
        $item = Item::find($id);
        $itemReserves = ItemReserve::where('item_id', $id)->get();
        $erro = false;
        $ids = [];
        foreach ($itemReserves as $itemReserve) {
            $ids[] = $itemReserve->reserve_id;
        }

        $reserves = Reserve::whereIn('id', $ids)->get();

        if (!session()->has('reserve')) {
            return redirect()->route('reserve.index')->with('warning', 'Deve iniciar uma reserva para poder adicionar itens!');
        }

        $dataInicio = session()->get('reserve.start_date');
        $dataFim = session()->get('reserve.end_date');
        $quantidade = $request->input('quantity', 1);
        // Conte o número de itens disponíveis com o mesmo nome
        $availableItems = Item::where('nome', $item->nome)->get();
        $itemCount = $availableItems->count();
        $existingItems = session()->get('reserve.itens', []);

        if (!is_null($item->kit_id)) {

            $kit = Kit::find($item->kit_id);

            $kitReserves = KitReserve::where('kit_id', $kit->id)->get();

            $ids = [];
            foreach ($kitReserves as $kitReserve) {
                $ids[] = $kitReserve->reserve_id;
            }

            $reserves2 = Reserve::whereIn('id', $ids)->get();

            $dataInicio = session()->get('reserve.start_date');
            $dataFim = session()->get('reserve.end_date');

            foreach ($reserves2 as $reserve) {
                if ($reserve->reserve_state_id != 3 && $reserve->reserve_state_id != 5 && $reserve->reserve_state_id != 6) {
                    if ($reserve->ciclica_id == 1) {
                        if (
                            $dataInicio >= $reserve->start_date && $dataInicio <= $reserve->end_date
                            || $dataFim >= $reserve->start_date && $dataFim <= $reserve->end_date
                            || $dataInicio <= $reserve->start_date && $dataInicio <= $reserve->end_date
                            && $dataFim >= $reserve->start_date && $dataFim >= $reserve->end_date
                        ) {
                            $erro = true;
                        }
                    } else {

                        $dayOfWeek = date('w', strtotime($reserve->start_date));
                        $dayOfWeekCiclica = $reserve->ciclica_id - 2;

                        if ($dayOfWeekCiclica > $dayOfWeek) {
                            for ($i = 1; $i < 7; $i++) {
                                if ($dayOfWeekCiclica == $dayOfWeek + $i) {
                                    if ($i == 1) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 1 day"));
                                    } else if ($i == 2) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 2 days"));
                                    } else if ($i == 3) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 3 days"));
                                    } else if ($i == 4) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 4 days"));
                                    } else if ($i == 5) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 5 days"));
                                    } else if ($i == 6) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 6 days"));
                                    }
                                }
                            }
                        } else if ($dayOfWeekCiclica < $dayOfWeek) {
                            for ($i = 1; $i < 7; $i++) {
                                if ($dayOfWeekCiclica == $dayOfWeek - $i) {
                                    if ($i == 1) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 6 day"));
                                    } else if ($i == 2) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 5 days"));
                                    } else if ($i == 3) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 4 days"));
                                    } else if ($i == 4) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 3 days"));
                                    } else if ($i == 5) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 2 days"));
                                    } else if ($i == 6) {
                                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 1 days"));
                                    }
                                }
                            }
                        } else {
                            $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date));
                        }

                        $datas = [];
                        $datas[] = $dataInicioCiclica;
                        $dataCiclica = $dataInicioCiclica;

                        while ($reserve->end_date >= $dataCiclica) {
                            $dataCiclica = date("Y-m-d", strtotime($dataCiclica . "+ 7 days"));
                            if ($reserve->end_date >= $dataCiclica) {
                                $datas[] = $dataCiclica;
                            }
                        }

                        for ($j = 0; $j < count($datas); $j++) {
                            if ($dataInicio <= $datas[$j] && $dataFim >= $datas[$j]) {
                                $erro = true;
                            }
                        }
                    }
                }
            }
        }

        foreach ($reserves as $reserve) {
            if ($reserve->reserve_state_id != 3 && $reserve->reserve_state_id != 5 && $reserve->reserve_state_id != 6) {
                if ($reserve->ciclica_id == 1) {
                    if (
                        $dataInicio >= $reserve->start_date && $dataInicio <= $reserve->end_date
                        || $dataFim >= $reserve->start_date && $dataFim <= $reserve->end_date
                        || $dataInicio <= $reserve->start_date && $dataInicio <= $reserve->end_date
                        && $dataFim >= $reserve->start_date && $dataFim >= $reserve->end_date
                    ) {
                        $erro = true;
                    }
                } else {

                    $dayOfWeek = date('w', strtotime($reserve->start_date));
                    $dayOfWeekCiclica = $reserve->ciclica_id - 2;

                    if ($dayOfWeekCiclica > $dayOfWeek) {
                        for ($i = 1; $i < 7; $i++) {
                            if ($dayOfWeekCiclica == $dayOfWeek + $i) {
                                if ($i == 1) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 1 day"));
                                } else if ($i == 2) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 2 days"));
                                } else if ($i == 3) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 3 days"));
                                } else if ($i == 4) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 4 days"));
                                } else if ($i == 5) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 5 days"));
                                } else if ($i == 6) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 6 days"));
                                }
                            }
                        }
                    } else if ($dayOfWeekCiclica < $dayOfWeek) {
                        for ($i = 1; $i < 7; $i++) {
                            if ($dayOfWeekCiclica == $dayOfWeek - $i) {
                                if ($i == 1) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 6 day"));
                                } else if ($i == 2) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 5 days"));
                                } else if ($i == 3) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 4 days"));
                                } else if ($i == 4) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 3 days"));
                                } else if ($i == 5) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 2 days"));
                                } else if ($i == 6) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 1 days"));
                                }
                            }
                        }
                    } else {
                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date));
                    }

                    $datas = [];
                    $datas[] = $dataInicioCiclica;
                    $dataCiclica = $dataInicioCiclica;

                    while ($reserve->end_date >= $dataCiclica) {
                        $dataCiclica = date("Y-m-d", strtotime($dataCiclica . "+ 7 days"));
                        if ($reserve->end_date >= $dataCiclica) {
                            $datas[] = $dataCiclica;
                        }
                    }

                    for ($j = 0; $j < count($datas); $j++) {
                        if ($dataInicio <= $datas[$j] && $dataFim >= $datas[$j]) {
                            $erro = true;
                        }
                    }
                }
            }
        }

        if ($erro) {
            return redirect()->route( 'user.categoria.index', $item->categoria_id )->with('toast_error', 'Item indisponível nas datas selecionadas!');
        }
        
        // Verifique a quantidade de itens já reservados com o mesmo nome
        $reservedCount = count(array_filter($existingItems, function($i) use ($item) {
            return $i->nome == $item->nome;
        }));

        // Verifique se a quantidade desejada está disponível
        if ($reservedCount + $quantidade > $itemCount) {
            return back()->with('warning', 'Quantidade desejada indisponível!');
        }

        // Adicione os itens à reserva
        $itemsToAdd = [];

        foreach ($availableItems as $availableItem) {
            $liaCodeExists = false;

            // Comparar lia_code dos existingItems com o lia_code do availableItem
            foreach ($existingItems as $existingItem) {
                if ($existingItem['lia_code'] == $availableItem->lia_code) {
                    $liaCodeExists = true;
                }
            }

            // Se liaCodeExists for falso, adiciona o availableItem ao array de itemsToAdd
            if (!$liaCodeExists && count($itemsToAdd) < $quantidade) {
                $itemsToAdd[] = $availableItem;
            }

            // Se já adicionou a quantidade desejada, interrompe o loop
            if (count($itemsToAdd) >= $quantidade) {
                break;
            }
        }

        // Adicionar os itens do array itemsToAdd à reserva
        $addedCount = 0;
        foreach ($itemsToAdd as $itemToAdd) {
            session()->push('reserve.itens', $itemToAdd);
            session()->increment('reserve.cost', $itemToAdd->preco);
            $addedCount++;

            // Se já adicionou a quantidade desejada, interrompe o loop
            if ($addedCount >= $quantidade) {
                break;
            }
        }        
        return back()->with('toast_success', 'Item/s adicionado/s à reserva!');
    }

    public function addKit(Request $request,$id)
    {
        $idsItens = [];
        $kit = Kit::find($id);
        $itens = Item::where('kit_id', $id)->get();
        foreach ($itens as $item) {
            $idsItens[] = $item->id;
        }
        $itemReserves = ItemReserve::whereIn('item_id', $idsItens)->get();
        $kitReserves = KitReserve::where('kit_id', $id)->get();
        $erro = false;
        $ids = [];
        foreach ($kitReserves as $kitReserve) {
            $ids[] = $kitReserve->reserve_id;
        }
        foreach ($itemReserves as $itemReserve) {
            $ids[] = $itemReserve->reserve_id;
        }

        $reserves = Reserve::whereIn('id', $ids)->get();

        if (!session()->has('reserve')) {
            return redirect()->route('reserve.index')->with('warning', 'Deve iniciar uma reserva para poder adicionar kits!');
        }

        $dataInicio = session()->get('reserve.start_date');
        $dataFim = session()->get('reserve.end_date');
        $quantidade = $request->input('quantity', 1);
        // Conte o número de kits disponíveis com o mesmo nome
        $availableKits = Kit::where('name', $kit->name)->get();
        $kitCount = $availableKits->count();
        $existingKits = session()->get('reserve.kits', []);

        foreach ($reserves as $reserve) {
            if ($reserve->reserve_state_id != 3 && $reserve->reserve_state_id != 5 && $reserve->reserve_state_id != 6) {
                if ($reserve->ciclica_id == 1) {
                    if (
                        $dataInicio >= $reserve->start_date && $dataInicio <= $reserve->end_date
                        || $dataFim >= $reserve->start_date && $dataFim <= $reserve->end_date
                        || $dataInicio <= $reserve->start_date && $dataInicio <= $reserve->end_date
                        && $dataFim >= $reserve->start_date && $dataFim >= $reserve->end_date
                    ) {
                        $erro = true;
                    }
                } else {

                    $dayOfWeek = date('w', strtotime($reserve->start_date));
                    $dayOfWeekCiclica = $reserve->ciclica_id - 2;

                    if ($dayOfWeekCiclica > $dayOfWeek) {
                        for ($i = 1; $i < 7; $i++) {
                            if ($dayOfWeekCiclica == $dayOfWeek + $i) {
                                if ($i == 1) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 1 day"));
                                } else if ($i == 2) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 2 days"));
                                } else if ($i == 3) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 3 days"));
                                } else if ($i == 4) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 4 days"));
                                } else if ($i == 5) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 5 days"));
                                } else if ($i == 6) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 6 days"));
                                }
                            }
                        }
                    } else if ($dayOfWeekCiclica < $dayOfWeek) {
                        for ($i = 1; $i < 7; $i++) {
                            if ($dayOfWeekCiclica == $dayOfWeek - $i) {
                                if ($i == 1) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 6 day"));
                                } else if ($i == 2) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 5 days"));
                                } else if ($i == 3) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 4 days"));
                                } else if ($i == 4) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 3 days"));
                                } else if ($i == 5) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 2 days"));
                                } else if ($i == 6) {
                                    $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date . "+ 1 days"));
                                }
                            }
                        }
                    } else {
                        $dataInicioCiclica = date("Y-m-d", strtotime($reserve->start_date));
                    }

                    $datas = [];
                    $datas[] = $dataInicioCiclica;
                    $dataCiclica = $dataInicioCiclica;

                    while ($reserve->end_date >= $dataCiclica) {
                        $dataCiclica = date("Y-m-d", strtotime($dataCiclica . "+ 7 days"));
                        if ($reserve->end_date >= $dataCiclica) {
                            $datas[] = $dataCiclica;
                        }
                    }

                    for ($j = 0; $j < count($datas); $j++) {
                        if ($dataInicio <= $datas[$j] && $dataFim >= $datas[$j]) {
                            $erro = true;
                        }
                    }
                }
            }
        }

        if ($erro) {
            return redirect('kits')->with('toast_error', 'Kit indisponível nas datas selecionadas!');
        }

        // Verifique a quantidade de itens já reservados com o mesmo nome
        $reservedCount = count(array_filter($existingKits, function($i) use ($kit) {
            return $i->name == $kit->name;
        }));

        // Verifique se a quantidade desejada está disponível
        if ($reservedCount + $quantidade > $kitCount) {
            return back()->with('warning', 'Quantidade desejada indisponível!');
        }

        // Adicione os itens à reserva
        $kitsToAdd = [];

        foreach ($availableKits as $availableKit) {
            $liaCodeExists = false;

            // Comparar lia_code dos existingkits com o lia_code do availableKit
            foreach ($existingKits as $existingKit) {
                if ($existingKit['lia_code'] == $availableKit->lia_code) {
                    $liaCodeExists = true;
                }
            }

            // Se liaCodeExists for falso, adiciona o availableItem ao array de itemsToAdd
            if (!$liaCodeExists && count($kitsToAdd) < $quantidade) {
                $kitsToAdd[] = $availableKit;
            }

            // Se já adicionou a quantidade desejada, interrompe o loop
            if (count($kitsToAdd) >= $quantidade) {
                break;
            }
        }

        // Adicionar os itens do array itemsToAdd à reserva
        $addedCount = 0;
        foreach ($kitsToAdd as $kitToAdd) {
            session()->push('reserve.kits', $kitToAdd);
            session()->increment('reserve.cost', $kitToAdd->price);
            $addedCount++;

            // Se já adicionou a quantidade desejada, interrompe o loop
            if ($addedCount >= $quantidade) {
                break;
            }
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

    // Lembra-te de importar as classes no topo do ficheiro, junto dos outros 'use':
// use App\Models\User;
// use App\Notifications\PedidoRequisicao;
// use Illuminate\Support\Facades\Notification;

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
        'cost' => session()->get('reserve.cost'),
        'reserve_state_id' => 1,
        'delivery_date' => null,
        'return_date' => null  
    ]);

    $costCenter = CostCenter::find(session()->get('reserve.cost_center_id'));
    $costCenter->total_cost += session()->get('reserve.cost');
    $costCenter->total_debt += session()->get('reserve.cost');
    $costCenter->save();

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
    // --------------------------------------

    session()->forget('reserve');

    return redirect('/')->with('success', 'Reserva efetuada com sucesso!');
}
}
