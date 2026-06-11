<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Kit;
use App\Models\Item;
use App\Models\Reserve;
use App\Models\ItemCategorie;
use App\Models\ItemReserve;
use App\Models\KitReserve;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\KitUnity;
use Carbon\Carbon;

class KitsController extends Controller
{

public function index()
    {
        $kits = $this->getKitsComStockFiltrado();

        return view('user.kits.listAll', ['kits' => $kits]);
    }

    public function all(Request $request)
    {
        if (!$request->ajax()) {
            return view('user.kits.listAll', ['kits' => $this->getKitsComStockFiltrado()]);
        }

        $output = '';
        $kits = $this->getKitsComStockFiltrado($request->search);

        if ($kits->count() > 0) {
            foreach ($kits as $kit) {
                // Se houver sessão de reserva, mostra a quantidade calculada individualmente por dia
                $txtDisponivel = session()->has('reserve') ? ' (Disponíveis: ' . $kit->quantidade_disponivel . ')' : '';

                $output .= '<div class="col mb-5">
                    <div class="card h-100">
                       <img class="card-img-top" src="../' . $kit->image . '" alt="..." />
                        <div class="card-body p-4">
                            <div class="text-center">
                                <h5 class="fw-bolder">' . htmlspecialchars($kit->name) . $txtDisponivel . '</h5>
                                <h6>' . htmlspecialchars($kit->description) . '</h6>
                                ' . number_format($kit->price, 2) . ' € / dia
                            </div>
                        </div>
                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                            <div class="text-center">
                                <a class="btn btn-outline-dark mt-auto" href="/kit/' . $kit->id . '">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        } else {
            $output = '<p>Nenhum item encontrado.</p>';
        }

        return response()->json($output);
    }

    /**
     * Centraliza a lógica de busca e cálculo de stock diário (Sem N+1 queries)
     */
    private function getKitsComStockFiltrado($search = null)
{
    $query = Kit::whereHas('kitUnities', function ($q) {
        $q->where('kit_unity_state_id', 1);
    });

    if ($search) {
        $query->where('name', 'LIKE', '%' . $search . '%');
    }

    $kits = $query->get();

    if (session()->has('reserve')) {
        $startDate = Carbon::parse(session()->get('reserve.start_date'));
        $endDate = Carbon::parse(session()->get('reserve.end_date'));
        $sessionCiclicaId = (int) session()->get('reserve.ciclica_id', 1);

        // 1. GERAR O CALENDÁRIO DO UTILIZADOR ATUAL
        $periodoDatas = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Se a reserva atual for normal (1) OU se o dia da semana coincidir com a cíclica escolhida
            if ($sessionCiclicaId === 1 || $date->dayOfWeek === ($sessionCiclicaId - 2)) {
                $periodoDatas[] = [
                    'data' => $date->format('Y-m-d'),
                    'dayOfWeek' => $date->dayOfWeek // Guarda o dia da semana (0 a 6)
                ];
            }
        }

        // 2. BUSCAR RESERVAS NO MESMO INTERVALO (Adicionado o campo 'reserves.ciclica_id')
        $reservasOcupantes = DB::table('kit_reserve')
            ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
            ->whereIn('reserves.reserve_state_id', [1, 2, 7])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('reserves.start_date', [$startDate, $endDate])
                  ->orWhereBetween('reserves.end_date', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('reserves.start_date', '<=', $startDate)
                          ->where('reserves.end_date', '>=', $endDate);
                  });
            })
            ->select('kit_reserve.kit_id', 'reserves.start_date', 'reserves.end_date', 'kit_reserve.quantity', 'reserves.ciclica_id')
            ->get();

        $totaisUnidades = KitUnity::where('kit_unity_state_id', 1)
            ->select('kit_id', DB::raw('count(*) as total'))
            ->groupBy('kit_id')
            ->pluck('total', 'kit_id');

        // 3. FILTRAR OS KITS DIA-A-DIA COM A LÓGICA CÍCLICA
        $kits = $kits->filter(function ($kit) use ($periodoDatas, $reservasOcupantes, $totaisUnidades) {
            $totalFisico = $totaisUnidades->get($kit->id, 0);
            if ($totalFisico <= 0) return false;

            $minimoDisponivelNoPeriodo = $totalFisico;

            foreach ($periodoDatas as $pData) {
                $dia = $pData['data'];
                $diaSemanaAtual = $pData['dayOfWeek'];
                $ocupadosHoje = 0;

                foreach ($reservasOcupantes as $reserva) {
                    if ($reserva->kit_id == $kit->id) {
                        // Verifica se o dia está dentro do intervalo de datas da reserva antiga
                        if ($dia >= $reserva->start_date && $dia <= $reserva->end_date) {
                            
                            // CASO A: A reserva antiga era contínua (Ocupa todos os dias)
                            if ((int)$reserva->ciclica_id === 1) {
                                $ocupadosHoje += $reserva->quantity;
                            } 
                            // CASO B: A reserva antiga também era cíclica (Só ocupa se for o mesmo dia da semana)
                            else {
                                $diaSemanaReservaAntiga = (int)$reserva->ciclica_id - 2;
                                if ($diaSemanaAtual === $diaSemanaReservaAntiga) {
                                    $ocupadosHoje += $reserva->quantity;
                                }
                            }
                        }
                    }
                }

                $disponivelHoje = $totalFisico - $ocupadosHoje;
                if ($disponivelHoje < $minimoDisponivelNoPeriodo) {
                    $minimoDisponivelNoPeriodo = $disponivelHoje;
                }
            }

            $kit->quantidade_disponivel = $minimoDisponivelNoPeriodo;
            return $minimoDisponivelNoPeriodo > 0;
        });
    }

    return $kits;
}

    public function show($id)
{
    $kit = Kit::findOrFail($id);
    $kitCount = KitUnity::where('kit_id', $kit->id)->where('kit_unity_state_id', 1)->count();

    $quantidadeDisponivel = $kitCount; // Por defeito, assume o total

    if (session()->has('reserve')) {
        // Vai buscar a lista de todos os kits já filtrados por data
        $kitsFiltrados = $this->getKitsComStockFiltrado();
        
        // Procura o kit atual dentro dessa lista filtrada
        $kitNoPeriodo = $kitsFiltrados->firstWhere('id', $id);
        
        // Se o encontrar, a quantidade disponível passa a ser o stock real calculado
        $quantidadeDisponivel = $kitNoPeriodo ? $kitNoPeriodo->quantidade_disponivel : 0;
    }
    $today = Carbon::today();

    $reservas = DB::table('kit_reserve')
        ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
        ->where('kit_reserve.kit_id', $id)
        ->whereIn('reserves.reserve_state_id', [1, 2, 7])
        ->whereDate('reserves.end_date', '>=', $today)
        ->select('reserves.start_date', 'reserves.end_date', 'kit_reserve.quantity', 'reserves.ciclica_id') // Importante trazer o ciclica_id
        ->get();

    $ocupacaoPorDia = [];
    foreach ($reservas as $reserva) {
        $inicio = Carbon::parse($reserva->start_date);
        $fim = Carbon::parse($reserva->end_date);

        while ($inicio <= $fim) {
            $ciclicaId = (int)$reserva->ciclica_id;

            // Se for normal (1) OU se for cíclica e o dia da semana atual bater com a regra da cíclica (id - 2)
            if ($ciclicaId === 1 || $inicio->dayOfWeek === ($ciclicaId - 2)) {
                $dia = $inicio->format('Y-m-d');
                $ocupacaoPorDia[$dia] = ($ocupacaoPorDia[$dia] ?? 0) + $reserva->quantity;
            }
            $inicio->addDay();
        }
    }

    $reservasFormatted = [];
    foreach ($ocupacaoPorDia as $dia => $ocupados) {
        // Bloqueia o dia no calendário visual apenas se a soma de todas as reservas esgotar o stock!
        if ($ocupados >= $kitCount) {
            $reservasFormatted[] = [
                'start_date' => Carbon::parse($dia)->format('d/m/Y'),
                'end_date'   => Carbon::parse($dia)->format('d/m/Y')
            ];
        }
    }

    return view('user.kits.info', [
        'kit' => $kit,
        'kitCount' => $kitCount,
        'quantidadeDisponivel' => $quantidadeDisponivel,
        'reservas' => $reservasFormatted
    ]);
}
    


    /*  
    public function index()
    {
        if (session()->has('reserve')) {
            $idsKitsDisponiveis = [];
            $kits = Kit::all();
            foreach ($kits as $kit) {
                if (KitsController::checkKit($kit->id)) {
                    $idsKitsDisponiveis[] = $kit->id;
                }
            }
            
            $KitsDisponiveis = Kit::whereIn('id', $idsKitsDisponiveis)
                                ->where('kit_state_id', '=', 1)
                                ->get();
            
            // Agrupar os kits pelo nome
            $KitsDisponiveis = $KitsDisponiveis->groupBy('name')->map(function ($group) {
                return $group->first();
            });

            return view('user.kits.listDisp', ['kits' => $KitsDisponiveis]);

        }else {
            $kits = Kit::where('kit_state_id', '=', 1)
                        ->get();

            // Agrupar os kits pelo nome
            $kits = $kits->groupBy('name')->map(function ($group) {
                return $group->first();
            });

            return view('user.kits.listAll', ['kits' => $kits]);
        }
            
    }


    public function show($id)
    {
        // Encontra o kit específico
        $kit = Kit::find($id);

        // Verifica quantos kits estão disponíveis com o mesmo nome
        $kitCount = Kit::where('name', $kit->name)
            //->where('kit_state_id', 1) // Verifica se o kit está disponível
            ->count();

        $today = \Carbon\Carbon::today();

        // Obtém as reservas para o kit específico com as condicionantes de reserve_state_id
        $reservas = DB::table('kit_reserve')
        ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
        ->where('kit_reserve.kit_id', $id)
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

        // Retorna a visualização com os dados
        return view('user.kits.info', [
            'kit' => $kit,
            'categoria' => ItemCategorie::all(),
            'kitCount' => $kitCount,
            'reservas' => $reservasFormatted
        ]);
    }
    

    public function disponivel(Request $request)
    {
        $idsKitsDisponiveis = [];
        $kits = Kit::all();
        foreach ($kits as $kit) {
            if (KitsController::checkKit($kit->id)) {
                $idsKitsDisponiveis[] = $kit->id;
            }
        }
        if ($request->ajax()) {
            $output = '';

            // Consulta os itens disponíveis conforme a pesquisa
            $kitsDisponiveis = Kit::whereIn('id', $idsKitsDisponiveis)
                ->where('name', 'LIKE', '%' . $request->search . '%')
                ->where('kit_state_id', '=', 1)
                ->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();

            // Constrói o HTML para cada item encontrado
            if ($kitsDisponiveis->count() > 0) {
                // Agrupar os itens pelo nome
                $kitsDisponiveis = $kitsDisponiveis->groupBy('name')->map(function ($group) {
                    return $group->first();
                });
                foreach ($kitsDisponiveis as $kit) {
                    $output .= '<div class="col mb-5">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../' . $kit->image . '" alt="..." />
                                        <div class="card-body p-4">
                                            <div class="text-center">
                                                <h5 class="fw-bolder">' . $kit->name . '</h5>
                                                <h6>' . $kit->description . '</h6>
                                                ' . $kit->price . ' € / dia
                                            </div>
                                        </div>
                                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                            <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/item/' . $kit->id . '">Ver Detalhes</a></div>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }else {
            $KitsDisponiveis = Kit::whereIn('id', $idsKitsDisponiveis)
                                ->where('kit_state_id', '=', 1)
                                ->get();
        }

        // Agrupar os kits pelo nome
        $KitsDisponiveis = $KitsDisponiveis->groupBy('name')->map(function ($group) {
            return $group->first();
        });

        return view('user.kits.listDisp', ['kits' => $KitsDisponiveis]);
    }

    public function indisponivel(Request $request)
    {
        $idsKitsIndisponiveis = [];
        $kits = Kit::all();
        foreach ($kits as $kit) {
            if (!KitsController::checkKit($kit->id)) {
                $idsKitsIndisponiveis[] = $kit->id;
            }
        }
        if ($request->ajax()) {
            $output = '';

            // Consulta os itens indisponíveis conforme a pesquisa
            $kitsIndisponiveis = Kit::whereIn('id', $idsKitsIndisponiveis)
                ->where('name', 'LIKE', '%' . $request->search . '%')
                ->where('kit_state_id', '=', 1)
                ->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();

            // Constrói o HTML para cada item encontrado
            if ($kitsIndisponiveis->count() > 0) {
                // Agrupar os itens pelo nome
                $kitsIndisponiveis = $kitsIndisponiveis->groupBy('name')->map(function ($group) {
                    return $group->first();
                });
                foreach ($kitsIndisponiveis as $kit) {
                    $output .= '<div class="col mb-5">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../' . $kit->image . '" alt="..." />
                                        <div class="card-body p-4">
                                            <div class="text-center">
                                                <h5 class="fw-bolder">' . $kit->name . '</h5>
                                                <h6>' . $kit->description . '</h6>
                                                ' . $kit->price . ' € / dia
                                            </div>
                                        </div>
                                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                            <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/item/' . $kit->id . '">Ver Detalhes</a></div>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }else {
            $KitsIndisponiveis = Kit::whereIn('id', $idsKitsIndisponiveis)
                                ->where('kit_state_id', '=', 1)
                                ->get();
        }

        // Agrupar os kits pelo nome
        $KitsIndisponiveis = $KitsIndisponiveis->groupBy('name')->map(function ($group) {
            return $group->first();
        });

        return view('user.kits.listIndisp', ['kits' => $KitsIndisponiveis]);
    }

    public function all(Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Consulta os itens disponíveis conforme a pesquisa
            $kits = Kit::where('name', 'LIKE', '%' . $request->search . '%')
                ->where('kit_state_id', '=', 1)
                ->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();

            // Constrói o HTML para cada item encontrado
            if ($kits->count() > 0) {
                // Agrupar os itens pelo nome
                $kits = $kits->groupBy('name')->map(function ($group) {
                    return $group->first();
                });
                foreach ($kits as $kit) {
                    $output .= '<div class="col mb-5">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../' . $kit->image . '" alt="..." />
                                        <div class="card-body p-4">
                                            <div class="text-center">
                                                <h5 class="fw-bolder">' . $kit->name . '</h5>
                                                <h6>' . $kit->description . '</h6>
                                                ' . $kit->price . ' € / dia
                                            </div>
                                        </div>
                                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                            <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="/item/' . $kit->id . '">Ver Detalhes</a></div>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }else {
            $kits = Kit::where('kit_state_id', '=', 1)
                        ->get();
        }

        // Agrupar os kits pelo nome
        $kits = $kits->groupBy('name')->map(function ($group) {
            return $group->first();
        });

        return view('user.kits.listAll', ['kits' => $kits]);
    }

    public function checkKit($id)
    {
        $idsItens = [];
        $itens = Item::where('kit_id', $id)->get();
        foreach ($itens as $item) {
            $idsItens[] = $item->id;
        }
        $itemReserves = ItemReserve::whereIn('item_id', $idsItens)->get();
        $kitReserves = KitReserve::where('kit_id', $id)->get();
        $ids = [];
        foreach ($kitReserves as $kitReserve) {
            $ids[] = $kitReserve->reserve_id;
        }
        foreach ($itemReserves as $itemReserve) {
            $ids[] = $itemReserve->reserve_id;
        }

        $reserves = Reserve::whereIn('id', $ids)->get();

        $dataInicio = session()->get('reserve.start_date');
        $dataFim = session()->get('reserve.end_date');

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
*/
    }
