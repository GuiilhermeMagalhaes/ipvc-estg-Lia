<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Kit;
use App\Models\KitUnity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitsController extends Controller
{
    // Rota base (/kits) redireciona para a lógica de ver todos
    public function index(Request $request)
    {
        return $this->disponivel($request);
    }

    // 1. PÁGINA: TODOS OS KITS
    public function all(Request $request)
    {
        $kits = $this->getKitsComStockFiltrado($request->search, 'all');

        if ($request->ajax()) {
            return response()->json($this->gerarHtmlParaPesquisa($kits));
        }

        return view('user.kits.listAll', ['kits' => $kits]);
    }

    // 2. PÁGINA: KITS DISPONÍVEIS
    public function disponivel(Request $request)
    {
        $kits = $this->getKitsComStockFiltrado($request->search, 'available');

        if ($request->ajax()) {
            return response()->json($this->gerarHtmlParaPesquisa($kits));
        }

        return view('user.kits.listDisp', ['kits' => $kits]);
    }

    // 3. PÁGINA: KITS INDISPONÍVEIS
    public function indisponivel(Request $request)
    {
        $kits = $this->getKitsComStockFiltrado($request->search, 'unavailable');

        if ($request->ajax()) {
            return response()->json($this->gerarHtmlParaPesquisa($kits));
        }

        return view('user.kits.listIndisp', ['kits' => $kits]);
    }


    /**
     * MOTOR CENTRAL: Busca o stock filtrando por pesquisa, disponibilidade e calendário
     */
    private function getKitsComStockFiltrado($search = null, $filter = 'all')
    {
        // 1. Inicia a pesquisa pela base de dados
        $query = Kit::query();
        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }
        $kits = $query->get();

        $periodoDatas = [];
        $reservasOcupantes = collect();

        // 2. Só fazemos contas de calendário se houver uma reserva iniciada
        if (session()->has('reserve')) {
            $startDate = Carbon::parse(session()->get('reserve.start_date'));
            $endDate = Carbon::parse(session()->get('reserve.end_date'));
            $sessionCiclicaId = (int) session()->get('reserve.ciclica_id', 1);

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                if ($sessionCiclicaId === 1 || $date->dayOfWeek === ($sessionCiclicaId - 2)) {
                    $periodoDatas[] = [
                        'data' => $date->format('Y-m-d'),
                        'dayOfWeek' => $date->dayOfWeek
                    ];
                }
            }

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
        }

        // 3. Verifica quantas unidades físicas existem no laboratório que não estejam avariadas
        $totaisUnidades = KitUnity::where('kit_unity_state_id', 1)
            ->select('kit_id', DB::raw('count(*) as total'))
            ->groupBy('kit_id')
            ->pluck('total', 'kit_id');

        // 4. Lógica de Filtragem Master
        $kits = $kits->filter(function ($kit) use ($periodoDatas, $reservasOcupantes, $totaisUnidades, $filter) {
            $totalFisico = $totaisUnidades->get($kit->id, 0);
            $minimoDisponivelNoPeriodo = $totalFisico;

            // Se houver uma reserva, faz o desconto de equipamentos diário
            if ($totalFisico > 0 && !empty($periodoDatas)) {
                foreach ($periodoDatas as $pData) {
                    $dia = $pData['data'];
                    $diaSemanaAtual = $pData['dayOfWeek'];
                    $ocupadosHoje = 0;

                    foreach ($reservasOcupantes as $reserva) {
                        if ($reserva->kit_id == $kit->id && $dia >= $reserva->start_date && $dia <= $reserva->end_date) {
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
            } elseif ($totalFisico <= 0) {
                $minimoDisponivelNoPeriodo = 0;
            }

            $kit->quantidade_disponivel = $minimoDisponivelNoPeriodo;

            // Retorna consoante a página em que estamos (Disponível, Indisponível ou Todos)
            if ($filter === 'available') {
                return $minimoDisponivelNoPeriodo > 0;
            } elseif ($filter === 'unavailable') {
                return $minimoDisponivelNoPeriodo <= 0;
            }
            return true;
        });

        return $kits;
    }

    /**
     * Função auxiliar para desenhar o HTML durante a Pesquisa AJAX
     */
    private function gerarHtmlParaPesquisa($kits)
    {
        $output = '';

        if ($kits->count() > 0) {
            foreach ($kits as $kit) {
                $txtDisponivel = session()->has('reserve') ? ' (Disp: ' . $kit->quantidade_disponivel . ')' : '';

                $output .= '<div class="col mb-5">
                    <div class="card h-100" id="kit">
                        <img class="card-img-top rounded-top" src="../' . $kit->image . '" alt="..." />
                        <div class="card-body p-4">
                            <div class="text-center">
                                <h5 class="fw-bolder">' . htmlspecialchars($kit->name) . $txtDisponivel . '</h5>
                                <h6>' . htmlspecialchars($kit->description) . '</h6>
                                ' . number_format($kit->price_day, 2, ',', '.') . ' € / dia
                            </div>
                        </div>
                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                            <div class="text-center">
                                <a class="btn btn-outline-dark mt-auto" href="/kit/' . $kit->id . '" style="width: 140px;">Ver Detalhes</a>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        } else {
            // Desenha aquele ecrã de aviso profissional se não encontrar nada na barra de pesquisa!
            $output = '<div class="col-12 text-center" style="margin-top: 80px; margin-bottom: 80px;">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h3 class="text-muted font-weight-bold">Sem Resultados</h3>
                <p class="text-muted" style="font-size: 1.1rem;">Não encontrámos nenhum Kit com esse nome.</p>
            </div>';
        }

        return $output;
    }
    
    // Mostra os detalhes individuais de 1 Kit
    public function show($id)
    {
        $kit = Kit::findOrFail($id);
        $kitCount = KitUnity::where('kit_id', $kit->id)->where('kit_unity_state_id', 1)->count();

        $kitUnityExemplo = KitUnity::with('itemUnities.item')->where('kit_id', $id)->first();

        $quantidadeDisponivel = $kitCount; 

        if (session()->has('reserve')) {
            $kitsFiltrados = $this->getKitsComStockFiltrado(null, 'all');
            $kitNoPeriodo = $kitsFiltrados->firstWhere('id', $id);
            $quantidadeDisponivel = $kitNoPeriodo ? $kitNoPeriodo->quantidade_disponivel : 0;
        }

        $today = Carbon::today();

        $reservas = DB::table('kit_reserve')
            ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
            ->where('kit_reserve.kit_id', $id)
            ->whereIn('reserves.reserve_state_id', [1, 2, 7])
            ->whereDate('reserves.end_date', '>=', $today)
            ->select('reserves.start_date', 'reserves.end_date', 'kit_reserve.quantity', 'reserves.ciclica_id')
            ->get();

        $ocupacaoPorDia = [];
        foreach ($reservas as $reserva) {
            $inicio = Carbon::parse($reserva->start_date);
            $fim = Carbon::parse($reserva->end_date);

            while ($inicio <= $fim) {
                $ciclicaId = (int)$reserva->ciclica_id;
                if ($ciclicaId === 1 || $inicio->dayOfWeek === ($ciclicaId - 2)) {
                    $dia = $inicio->format('Y-m-d');
                    $ocupacaoPorDia[$dia] = ($ocupacaoPorDia[$dia] ?? 0) + $reserva->quantity;
                }
                $inicio->addDay();
            }
        }

        $reservasFormatted = [];
        foreach ($ocupacaoPorDia as $dia => $ocupados) {
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
            'reservas' => $reservasFormatted,
            'kitUnityExemplo' => $kitUnityExemplo
        ]);
    }
}