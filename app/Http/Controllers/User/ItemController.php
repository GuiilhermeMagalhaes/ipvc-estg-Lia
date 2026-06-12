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
use App\Models\ItemUnity;
use Carbon\Carbon;


class ItemController extends Controller
{
   public function index($id)
    {
        $itens = $this->getItensComStockFiltrado($id);

        $category = ItemCategorie::findOrFail($id);

        // ALTERAÇÃO: Passa a variável 'category' para a View
        return view('user.itens.listAll', [
            'itens' => $itens, 
            'category' => $category
        ]);

    }



    private function getItensComStockFiltrado($categoria_id, $search = null)
{


    $query = Item::where('categoria_id', '=', $categoria_id)
            ->whereHas('itemUnities', function ($q) {
                $q->where('item_unity_state_id', 1); // Ajusta o ID do estado ativo se for diferente de 1
            });


    if ($search) {
        // Ajustado para 'nome' que é o campo na tua tabela item
        $query->where('nome', 'LIKE', '%' . $search . '%');
    }

    $items = $query->get();

    if (session()->has('reserve')) {
        $startDate = Carbon::parse(session()->get('reserve.start_date'));
        $endDate = Carbon::parse(session()->get('reserve.end_date'));
        $sessionCiclicaId = (int) session()->get('reserve.ciclica_id', 1);

        // GERAR O CALENDÁRIO DO UTILIZADOR ATUAL
        $periodoDatas = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            if ($sessionCiclicaId === 1 || $date->dayOfWeek === ($sessionCiclicaId - 2)) {
                $periodoDatas[] = [
                    'data' => $date->format('Y-m-d'),
                    'dayOfWeek' => $date->dayOfWeek
                ];
            }
        }

        // BUSCAR RESERVAS DE ITENS NO MESMO INTERVALO
        $reservasOcupantes = DB::table('item_reserve')
            ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
            ->whereIn('reserves.reserve_state_id', [1, 2, 7])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('reserves.start_date', [$startDate, $endDate])
                  ->orWhereBetween('reserves.end_date', [$startDate, $endDate])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('reserves.start_date', '<=', $startDate)
                          ->where('reserves.end_date', '>=', $endDate);
                  });
            })
            ->select('item_reserve.item_id', 'reserves.start_date', 'reserves.end_date', 'item_reserve.quantity', 'reserves.ciclica_id')
            ->get();

        
        $totaisUnidades = DB::table('item_unity')
            ->where('item_state_id', 1) 
            ->select('item_id', DB::raw('count(*) as total'))
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        // FILTRAR OS ITENS DIA-A-DIA COM A LÓGICA CÍCLICA
        $items = $items->filter(function ($item) use ($periodoDatas, $reservasOcupantes, $totaisUnidades) {
            $totalFisico = $totaisUnidades->get($item->id, 0);
            if ($totalFisico <= 0) return false;

            $minimoDisponivelNoPeriodo = $totalFisico;

            foreach ($periodoDatas as $pData) {
                $dia = $pData['data'];
                $diaSemanaAtual = $pData['dayOfWeek'];
                $ocupadosHoje = 0;

                foreach ($reservasOcupantes as $reserva) {
                    if ($reserva->item_id == $item->id) {
                        // Verifica se o dia está dentro do intervalo de datas da reserva antiga
                        if ($dia >= $reserva->start_date && $dia <= $reserva->end_date) {
                            
                            // CASO A: A reserva antiga era contínua (Ocupa todos os dias)
                            if ((int)$reserva->ciclica_id === 1) {
                                $ocupadosHoje += $reserva->quantity;
                            } 
                            // CASO B: A reserva antiga também era cíclica (Só ocupa no mesmo dia da semana)
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

            // Injeta o atributo dinâmico para usares na View
            $item->quantidade_disponivel = $minimoDisponivelNoPeriodo;
            
            // Só deixa listar o item se sobrar pelo menos 1 unidade livre no período
            return $minimoDisponivelNoPeriodo > 0;
        });
    }

    return $items;
}


 public function all($id, Request $request)
{

    if (!$request->ajax()) {
            // ALTERAÇÃO: Adicionada a busca e envio da categoria aqui também por segurança
            $category = ItemCategorie::findOrFail($id);
            return view('user.itens.listAll', [
                'items' => $this->getItensComStockFiltrado($id),
                'category' => $category,
            ]);
        }

    $output = '';
    $items = $this->getItensComStockFiltrado($id, $request->search);

    if ($items->count() > 0) {
        foreach ($items as $item) {
            // Se houver sessão de reserva, adiciona o indicador de stock disponível no período
            $txtDisponivel = session()->has('reserve') ? ' (Disponíveis: ' . $item->quantidade_disponivel . ')' : '';

            $output .= '<div class="col mb-5">
                <div class="card h-100 kit-card">
                   <img class="card-img-top rounded-top" src="../' . $item->image . '" alt="..." />
                    <div class="card-body p-4">
                        <div class="text-center">
                            <h5 class="fw-bolder">' . htmlspecialchars($item->nome) . $txtDisponivel . '</h5>
                            <h6>' . htmlspecialchars($item->observation ?? '') . '</h6>
                            ' . number_format($item->price_day, 2, ',', '.') . ' € / dia
                        </div>
                    </div>
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                        <div class="text-center">
                            <a class="btn btn-outline-dark mt-auto" href="/item/' . $item->id . '" style="width: 140px;">Ver Detalhes</a>
                        </div>
                    </div>
                </div>
            </div>';
        }
    } else {
        $output = '<div class="col-12 text-center"><p>Nenhum item encontrado nesta categoria.</p></div>';
    }

    return response()->json($output);
}



    public function show($id)
{
    // 1. Procura o item ou falha se não existir
    $item = Item::findOrFail($id);
    
    // Conta quantas unidades físicas ativas este item tem no total
    $itemCount = DB::table('item_unity')
        ->where('item_id', $item->id)
        ->where('item_unity_state_id', 1)
        ->count();

    $quantidadeDisponivel = $itemCount; // Por defeito, assume o total físico

    if (session()->has('reserve')) {
        // Vai buscar a lista de todos os itens já filtrados por data
        $itemsFiltrados = $this->getItensComStockFiltrado($item->categoria_id);
        
        // Procura o item atual dentro dessa lista filtrada
        $itemNoPeriodo = $itemsFiltrados->firstWhere('id', $id);
        
        // Se o encontrar, a quantidade disponível passa a ser o stock real calculado para as datas da sessão
        $quantidadeDisponivel = $itemNoPeriodo ? $itemNoPeriodo->quantidade_disponivel : 0;
    }
    
    $today = Carbon::today();

    // 2. Procura todas as reservas futuras ou em andamento que usem este item específico
    $reservas = DB::table('item_reserve')
        ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
        ->where('item_reserve.item_id', $id)
        ->whereIn('reserves.reserve_state_id', [1, 2, 7])
        ->whereDate('reserves.end_date', '>=', $today)
        ->select('reserves.start_date', 'reserves.end_date', 'item_reserve.quantity', 'reserves.ciclica_id')
        ->get();

    // 3. Mapeia a ocupação diária para o calendário visual do item
    $ocupacaoPorDia = [];
    foreach ($reservas as $reserva) {
        $inicio = Carbon::parse($reserva->start_date);
        $fim = Carbon::parse($reserva->end_date);

        while ($inicio <= $fim) {
            $ciclicaId = (int)$reserva->ciclica_id;

            // Lógica Cíclica: Se for normal (1) OU se a regra cíclica coincidir com o dia da semana atual
            if ($ciclicaId === 1 || $inicio->dayOfWeek === ($ciclicaId - 2)) {
                $dia = $inicio->format('Y-m-d');
                $ocupacaoPorDia[$dia] = ($ocupacaoPorDia[$dia] ?? 0) + $reserva->quantity;
            }
            $inicio->addDay();
        }
    }

    // 4. Formata os dias totalmente esgotados para o Flatpickr/calendário da View
    $reservasFormatted = [];
    foreach ($ocupacaoPorDia as $dia => $ocupados) {
        // Bloqueia o dia no calendário visual apenas se a soma das quantidades reservadas esgotar o stock físico
        if ($ocupados >= $itemCount) {
            $reservasFormatted[] = [
                'start_date' => Carbon::parse($dia)->format('d/m/Y'),
                'end_date'   => Carbon::parse($dia)->format('d/m/Y')
            ];
        }
    }

    // 5. Retorna a view de detalhes do item com as variáveis adaptadas
    return view('user.itens.info', [
        'item' => $item,
        'itemCount' => $itemCount,
        'quantidadeDisponivel' => $quantidadeDisponivel,
        'reservas' => $reservasFormatted
    ]);
}

}