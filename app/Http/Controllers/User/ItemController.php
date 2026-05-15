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
                if (ItemController::checkItem($item->id)) {
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
            if (ItemController::checkItem($item->id)) {
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
            if (!ItemController::checkItem($item->id)) {
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

        // Conta a quantidade de itens disponíveis com o mesmo nome
        $itemCount = Item::where('nome', $item->nome)
            ->where('item_state_id', 1) // Considera apenas itens disponíveis
            ->count();

        $today = \Carbon\Carbon::today();

        // Obtém as reservas para o item específico com as condicionantes de reserve_state_id
        $reservas = DB::table('item_reserve')
            ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
            ->where('item_reserve.item_id', $id)
            ->whereIn('reserves.reserve_state_id', [1, 2, 7]) // Adiciona a condição para reserve_state_id
            ->whereDate('reserves.end_date', '>=', $today)
            ->select('reserves.start_date', 'reserves.end_date')
            ->get();

        // Converte as datas para o formato dia/mes/ano
        $reservasFormatted = $reservas->map(function ($reserva) {
            return [
                'start_date' => \Carbon\Carbon::parse($reserva->start_date)->format('d/m/Y'),
                'end_date' => \Carbon\Carbon::parse($reserva->end_date)->format('d/m/Y')
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
        $itemReserves = ItemReserve::where('item_id', $id)->get();
        $ids = [];
        foreach ($itemReserves as $itemReserve) {
            $ids[] = $itemReserve->reserve_id;
        }

        $reserves = Reserve::whereIn('id', $ids)->get();

        $dataInicio = session()->get('reserve.start_date');
        $dataFim = session()->get('reserve.end_date');


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
                            return false;
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
                                return false;
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
                        return false;
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
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
}
