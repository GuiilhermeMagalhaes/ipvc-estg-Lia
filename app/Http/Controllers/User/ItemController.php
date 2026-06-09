<?php

namespace App\Http\Controllers\User;

use App\Models\Item;
use App\Models\ItemReserve;
use App\Models\Kit;
use App\Models\KitReserve;
use App\Models\Reserve;
use App\Http\Controllers\Controller;
use App\Models\ItemCategorie;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ItemController extends Controller
{
    public function index($id)
    {
        if (session()->has('reserve')) {
            $idsItensDisponiveis = [];
            $itens = Item::all();
            foreach ($itens as $item) {
                if ($this->checkItem($item->id)) {
                    $idsItensDisponiveis[] = $item->id;
                }
            }
            $itensDisponiveis = Item::whereIn('id', $idsItensDisponiveis)
                ->where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->get();

            // Agrupar os itens pelo nome
            $itensDisponiveis = $itensDisponiveis->groupBy('nome')->map(function ($group) {
                return $group->first();
            });

            return view('user.itens.listDisp', ['itens' => $itensDisponiveis, 'category' => ItemCategorie::find($id)]);
        } else {
            $itens = Item::where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->get();

            // Agrupar os itens pelo nome
            $itens = $itens->groupBy('nome')->map(function ($group) {
                return $group->first();
            });

            return view('user.itens.listAll', ['itens' => $itens, 'category' => ItemCategorie::find($id)]);
        }
    }

    public function disponivel($id, Request $request)
    {
        $idsItensDisponiveis = [];
        $itens = Item::all();
        foreach ($itens as $item) {
            if ($this->checkItem($item->id)) {
                $idsItensDisponiveis[] = $item->id;
            }
        }
        if ($request->ajax()) {
            $output = '';

            // Consulta os itens disponíveis conforme a pesquisa
            $itensDisponiveis = Item::whereIn('id', $idsItensDisponiveis)
                ->where('nome', 'LIKE', '%' . $request->search . '%')
                ->where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->whereRaw("LOWER(nome) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();

            // Constrói o HTML para cada item encontrado
            if ($itensDisponiveis->count() > 0) {
                // Agrupar os itens pelo nome
                $itensDisponiveis = $itensDisponiveis->groupBy('nome')->map(function ($group) {
                    return $group->first();
                });
                foreach ($itensDisponiveis as $item) {
                    $output .= '<div class="col mb-5">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../' . $item->image . '" alt="..." />
                                        <div class="card-body p-4">
                                            <div class="text-center">
                                                <h5 class="fw-bolder">' . $item->nome . '</h5>
                                                ' . $item->preco . ' € / dia
                                            </div>
                                        </div>
                                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                            <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/item/' . $item->id . '">Ver Detalhes</a></div>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        } else {
            $itensDisponiveis = Item::whereIn('id', $idsItensDisponiveis)
                ->where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->get();
        }

        // Agrupar os itens pelo nome
        $itensDisponiveis = $itensDisponiveis->groupBy('nome')->map(function ($group) {
            return $group->first();
        });

        return view('user.itens.listDisp', ['itens' => $itensDisponiveis, 'category' => ItemCategorie::find($id)]);
    }

    public function indisponivel($id, Request $request)
    {
        $idsItensIndisponiveis = [];
        $itens = Item::all();
        foreach ($itens as $item) {
            if (!$this->checkItem($item->id)) {
                $idsItensIndisponiveis[] = $item->id;
            }
        }
        if ($request->ajax()) {
            $output = '';

            // Consulta os itens disponíveis conforme a pesquisa
            $itensIndisponiveis = Item::whereIn('id', $idsItensIndisponiveis)
                ->where('nome', 'LIKE', '%' . $request->search . '%')
                ->where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->whereRaw("LOWER(nome) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();

            // Constrói o HTML para cada item encontrado
            if ($itensIndisponiveis->count() > 0) {
                // Agrupar os itens pelo nome
                $itensIndisponiveis = $itensIndisponiveis->groupBy('nome')->map(function ($group) {
                    return $group->first();
                });
                foreach ($itensIndisponiveis as $item) {
                    $output .= '<div class="col mb-5">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../' . $item->image . '" alt="..." />
                                        <div class="card-body p-4">
                                            <div class="text-center">
                                                <h5 class="fw-bolder">' . $item->nome . '</h5>
                                                ' . $item->preco . ' € / dia
                                            </div>
                                        </div>
                                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                            <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/item/' . $item->id . '">Ver Detalhes</a></div>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        } else {
            $itensIndisponiveis = Item::whereIn('id', $idsItensIndisponiveis)
                ->where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->get();
        }

        // Agrupar os itens pelo nome
        $itensIndisponiveis = $itensIndisponiveis->groupBy('nome')->map(function ($group) {
            return $group->first();
        });

        return view('user.itens.listIndisp', ['itens' => $itensIndisponiveis, 'category' => ItemCategorie::find($id)]);
    }

    public function all($id, Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Consulta os itens conforme a pesquisa
            $itens = Item::where('nome', 'LIKE', '%' . $request->search . '%')
                ->where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->whereRaw("LOWER(nome) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();

            // Constrói o HTML para cada item encontrado
            if ($itens->count() > 0) {
                // Agrupar os itens pelo nome
                $itens = $itens->groupBy('nome')->map(function ($group) {
                    return $group->first();
                });
                foreach ($itens as $item) {
                    $output .= '<div class="col mb-5">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../' . $item->image . '" alt="..." />
                                        <div class="card-body p-4">
                                            <div class="text-center">
                                                <h5 class="fw-bolder">' . $item->nome . '</h5>
                                                ' . $item->preco . ' € / dia
                                            </div>
                                        </div>
                                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                            <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/item/' . $item->id . '">Ver Detalhes</a></div>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        } else {
            $itens = Item::where('item_state_id', '=', 1)
                ->where('categoria_id', '=', $id)
                ->get();
        }

        // Agrupar os itens pelo nome
        $itens = $itens->groupBy('nome')->map(function ($group) {
            return $group->first();
        });

        return view('user.itens.listAll', ['itens' => $itens, 'category' => ItemCategorie::find($id)]);
    }

    public function show($id)
    {
        // Encontra o item específico e carrega a relação 'itemCategorie'
        $item = Item::with('itemCategorie')->findOrFail($id);

       // Conta quantas unidades físicas reais e operacionais existem para este item
        $itemCount = \App\Models\ItemUnity::where('item_id', $item->id)
            ->where('item_unity_state_id', 1)
            ->count();

        $today = \Carbon\Carbon::today();

        $reservas = DB::table('item_reserve')
            ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
            ->where('item_reserve.item_id', $id)
            ->whereIn('reserves.reserve_state_id', [1, 2, 7])
            ->whereDate('reserves.end_date', '>=', $today)
            ->select('reserves.start_date', 'reserves.end_date', 'reserves.ciclica_id') 
            ->get();

        // Converte as datas e passa o ciclica_id
        $reservasFormatted = $reservas->map(function ($reserva) {
            return [
                'start_date' => \Carbon\Carbon::parse($reserva->start_date)->format('Y-m-d'), // Usar formato Y-m-d costuma ser melhor para o JS
                'end_date' => \Carbon\Carbon::parse($reserva->end_date)->format('Y-m-d'),
                'ciclica_id' => $reserva->ciclica_id // <-- NOVO
            ];
        });

        // Passa os dados para a view
        return view('user.itens.info', [
            'item' => $item,
            'categoria' => ItemCategorie::all(),
            'itemCount' => $itemCount,
            'reservas' => $reservasFormatted
        ]);
    }

    public function checkItem($id)
    {
    $item = Item::find($id);
    $dataInicio = session()->get('reserve.start_date');
    $dataFim = session()->get('reserve.end_date');

    if (!$dataInicio || !$dataFim) {
        return true;
    }

    $totalUnidades = \App\Models\ItemUnity::where('item_id', $id)
        ->where('item_unity_state_id', 1)
        ->count();

    if ($totalUnidades == 0) {
        return false;
    }

    // Conta quantos deste item já estão no carrinho da sessão
    $itensNoCarrinho = session()->get('reserve.itens', []);
    $qtdNoCarrinho = count(array_filter($itensNoCarrinho, function($i) use ($id) {
        return $i->id == $id;
    }));

    $unidadesOcupadas = 0;
    $itemReserves = ItemReserve::where('item_id', $id)->pluck('reserve_id');
    $reserves = Reserve::whereIn('id', $itemReserves)->get();

    foreach ($reserves as $reserve) {
        if ($this->isPeriodoOcupado($reserve, $dataInicio, $dataFim)) {
            $qtdNestaReserva = ItemReserve::where('reserve_id', $reserve->id)->where('item_id', $id)->count();
            $unidadesOcupadas += $qtdNestaReserva;
        }
    }

    // Subtrai também o que está no carrinho
    return ($totalUnidades - $unidadesOcupadas - $qtdNoCarrinho) > 0;
    }

    private function isPeriodoOcupado($reserve, $dataInicio, $dataFim) 
    {
        // 1. Ignora reservas concluídas, canceladas ou rejeitadas
        if (in_array($reserve->reserve_state_id, [3, 5, 6])) return false;

        // 2. SE FOR UMA RESERVA NORMAL (NÃO CÍCLICA)
        if ($reserve->ciclica_id == 1) {
            return ($dataInicio <= $reserve->end_date && $dataFim >= $reserve->start_date);
        }

        // 3. SE FOR UMA RESERVA CÍCLICA
        // Calcula a primeira ocorrência do dia da semana escolhido dentro do intervalo
        $dayOfWeekCiclica = $reserve->ciclica_id - 2; 
        $startDay = date('w', strtotime($reserve->start_date));
        $diff = $dayOfWeekCiclica - $startDay;
        
        if ($diff < 0) {
            $diff += 7;
        }
        
        $dataAtual = date("Y-m-d", strtotime($reserve->start_date . "+ $diff days"));

        // Verifica semana a semana se há conflito
        while ($dataAtual <= $reserve->end_date) {
            if ($dataAtual >= $dataInicio && $dataAtual <= $dataFim) {
                return true; // Conflito encontrado!
            }
            $dataAtual = date("Y-m-d", strtotime($dataAtual . "+ 7 days"));
        }

        return false;
    }
}