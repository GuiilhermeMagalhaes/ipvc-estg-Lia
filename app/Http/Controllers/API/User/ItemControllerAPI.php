<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Kit;
use App\Models\ItemCategorie;
use App\Models\KitUnity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ItemControllerAPI extends Controller
{
    public function index(Request $request)
    {

    $search = $request->query('search');
        try {
            $estadoDisponivelId = 1;
            $categoriaId = $request->query('categoria_id', 'Todos');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $estadosBloqueantes = [1, 2, 4, 7];

            $startCarbon = $startDate ? Carbon::parse($startDate) : null;
            $endCarbon   = $endDate ? Carbon::parse($endDate) : null;

            $kitsDestaque = [];
            $itensLista = [];
            $paginacao = [];

            // CENÁRIO 1: TAB "KITS"
            if ($categoriaId === 'Kits') {
                $queryKits = Kit::has('kitUnities');


                if (!empty($search)) {
                    $queryKits->where('name', 'like', "%{$search}%");
                }

                $kitsPaginated = $queryKits->withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('kit_unity_state_id', $estadoDisponivelId);
                }])->paginate(10);

                $itensTratados = collect($kitsPaginated->items())->map(function ($kit) use ($startCarbon, $endCarbon, $estadosBloqueantes) {
                    $kit->nome = $kit->name;
                    $kit->model = $kit->description;
                    $totalFisico = $kit->kit_unities_count;

                    if ($startCarbon && $endCarbon) {
                        $totalFisico = $this->calcularStockMinimo('kit_reserve', 'kit_id', $kit->id, $totalFisico, $startCarbon, $endCarbon, $estadosBloqueantes);
                    }

                    $kit->item_unities_count = $totalFisico;
                    return $kit;
                });

                $itensLista = $itensTratados;
                $paginacao = [
                    'pagina_atual' => $kitsPaginated->currentPage(),
                    'ultima_pagina' => $kitsPaginated->lastPage(),
                    'tem_mais' => $kitsPaginated->hasMorePages()
                ];
            }
            // CENÁRIO 2: TODOS / OUTRA CATEGORIA
            else {
                // Destaques Horizontais (Kits)
                if ($request->query('page', 1) == 1 && $categoriaId === 'Todos' && empty($search)) {
                    $kitsDestaqueRaw = Kit::has('kitUnities')
                        ->withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                            $query->where('kit_unity_state_id', $estadoDisponivelId);
                        }])->get();

                    $kitsDestaque = collect($kitsDestaqueRaw)->map(function ($kit) use ($startCarbon, $endCarbon, $estadosBloqueantes) {
                        $totalFisico = $kit->kit_unities_count;
                        if ($startCarbon && $endCarbon) {
                            $totalFisico = $this->calcularStockMinimo('kit_reserve', 'kit_id', $kit->id, $totalFisico, $startCarbon, $endCarbon, $estadosBloqueantes);
                        }
                        $kit->kit_unities_count = $totalFisico;
                        return $kit;
                    });
                }

                $queryItens = Item::has('itemUnities')
                    ->withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                        $query->where('item_unity_state_id', $estadoDisponivelId);
                    }]);

                // 1. Aplica o filtro de pesquisa se existir
                if (!empty($search)) {
                    $queryItens->where(function ($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                    });
                }

                 {/*
                $queryItens->withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('item_unity_state_id', $estadoDisponivelId);
                }]);
*/}
                // 3. Aplica a categoria se não for "Todos"
                if ($categoriaId !== 'Todos') {
                    $queryItens->where('categoria_id', $categoriaId);
                }

                // 4. Faz a paginação final
                $itensPaginated = $queryItens->paginate(10);

                $itensLista = collect($itensPaginated->items())->map(function ($item) use ($startCarbon, $endCarbon, $estadosBloqueantes) {
                    $totalFisico = $item->item_unities_count;
                    if ($startCarbon && $endCarbon) {
                        $totalFisico = $this->calcularStockMinimo('item_reserve', 'item_id', $item->id, $totalFisico, $startCarbon, $endCarbon, $estadosBloqueantes);
                    }
                    $item->item_unities_count = $totalFisico;
                    return $item;
                });

                $paginacao = [
                    'pagina_atual' => $itensPaginated->currentPage(),
                    'ultima_pagina' => $itensPaginated->lastPage(),
                    'tem_mais' => $itensPaginated->hasMorePages()
                ];
            }

            $categorias = [];
            if ($request->query('page', 1) == 1) {
                $categorias = ItemCategorie::select('id', 'description')->get();
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'categorias' => $categorias,
                    'kits' => $kitsDestaque,
                    'itens' => $itensLista,
                    'paginacao' => $paginacao
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar o catálogo: ' . $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $id)
    {
        try {
            $estadoDisponivelId = 1;
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $estadosBloqueantes = [1, 2, 4, 7];
            $tipo = $request->query('tipo');

            $startCarbon = $startDate ? Carbon::parse($startDate) : null;
            $endCarbon   = $endDate ? Carbon::parse($endDate) : null;

            // === ITEM ===
            if ($tipo !== 'kit') {
                $item = Item::with(['itemCategorie' => function ($query) {
                    $query->select('id', 'description');
                }])->withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('item_unity_state_id', $estadoDisponivelId);
                }])->find($id);

                if ($item) {
                    $totalFisico = $item->item_unities_count;
                    if ($startCarbon && $endCarbon) {
                        $totalFisico = $this->calcularStockMinimo('item_reserve', 'item_id', $item->id, $totalFisico, $startCarbon, $endCarbon, $estadosBloqueantes);
                    }

                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'id'         => $item->id,
                            'nome'       => $item->nome,
                            'model'      => $item->model,
                            'categoria'  => $item->itemCategorie ? $item->itemCategorie->description : 'Geral',
                            'observacao' => $item->observation ?? 'Nenhuma observação',
                            'acessorios' => $item->acessorio ?? 'Nenhum acessório listado',
                            'quantidade' => $totalFisico,
                            'preco'      => $item->price_day ?? $item->preco ?? '20,00',
                            'image'      => $item->image,
                            'is_kit'     => false
                        ]
                    ], 200);
                }
            }

            // === KIT ===
            if ($tipo === 'kit' || $tipo === null) {
                $kit = Kit::withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('kit_unity_state_id', $estadoDisponivelId);
                }])->find($id);

                if ($kit) {
                    $totalFisicoKit = $kit->kit_unities_count;
                    if ($startCarbon && $endCarbon) {
                        $totalFisicoKit = $this->calcularStockMinimo('kit_reserve', 'kit_id', $kit->id, $totalFisicoKit, $startCarbon, $endCarbon, $estadosBloqueantes);
                    }

                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'id'         => $kit->id,
                            'nome'       => $kit->name,
                            'model'      => $kit->description,
                            'categoria'  => 'Kit Completo',
                            'observacao' => 'Conjunto de Equipamentos',
                            'acessorios' => 'Acessórios integrados no kit',
                            'quantidade' => $totalFisicoKit,
                            'preco'      => $kit->price_day ?? $kit->price ?? '35,00',
                            'image'      => $kit->image,
                            'is_kit'     => true
                        ]
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Equipamento ou Kit não encontrado.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar detalhes: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getDatasEsgotadas(Request $request, $id)
    {
        try {
            $estadoDisponivelId = 1;
            $estadosBloqueantes = [1, 2, 4, 7];
            $datasEsgotadas = [];
            $tipo = $request->query('tipo');

            $totalFisico = 0;
            $reservas = collect();
            $encontrado = false;

            // === ITEM ===
            if ($tipo !== 'kit') {
                $item = Item::withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('item_unity_state_id', $estadoDisponivelId);
                }])->find($id);

                if ($item) {
                    $encontrado = true;
                    $totalFisico = $item->item_unities_count;
                    $reservas = \DB::table('item_reserve')
                        ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
                        ->where('item_reserve.item_id', $id)
                        ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
                        ->where('reserves.end_date', '>=', now()->format('Y-m-d'))
                        ->select('reserves.start_date', 'reserves.end_date', 'item_reserve.quantity', 'reserves.ciclica_id')
                        ->get();
                }
            }

            // === KIT ===
            if (!$encontrado && ($tipo === 'kit' || $tipo === null)) {
                $kit = Kit::withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('kit_unity_state_id', $estadoDisponivelId);
                }])->find($id);

                if ($kit) {
                    $encontrado = true;
                    $totalFisico = $kit->kit_unities_count;
                    $reservas = \DB::table('kit_reserve')
                        ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
                        ->where('kit_reserve.kit_id', $id)
                        ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
                        ->where('reserves.end_date', '>=', now()->format('Y-m-d'))
                        ->select('reserves.start_date', 'reserves.end_date', 'kit_reserve.quantity', 'reserves.ciclica_id')
                        ->get();
                }
            }

            if (!$encontrado) {
                return response()->json(['status' => 'error', 'message' => 'Equipamento não encontrado.'], 404);
            }

            // Somar quantidades por dia (respeitando o padrão cíclico)
            $diasOcupacao = [];
            foreach ($reservas as $reserva) {
                $startDate = Carbon::parse($reserva->start_date);
                $endDate = Carbon::parse($reserva->end_date);
                $ehContinua = ($reserva->ciclica_id === null || (int) $reserva->ciclica_id === 1);

                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    // Se for cíclica, só conta o dia da semana correto
                    if (!$ehContinua && $date->dayOfWeek !== ((int) $reserva->ciclica_id - 2)) {
                        continue;
                    }
                    $dateString = $date->format('Y-m-d');
                    if (!isset($diasOcupacao[$dateString])) {
                        $diasOcupacao[$dateString] = 0;
                    }
                    $diasOcupacao[$dateString] += $reserva->quantity;
                }
            }

            // Dias esgotados
            foreach ($diasOcupacao as $data => $quantidadeOcupada) {
                if ($quantidadeOcupada >= $totalFisico) {
                    $datasEsgotadas[] = $data;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $datasEsgotadas,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao calcular datas: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Calcula o stock mínimo disponível de um item/kit num período,
     * respeitando o padrão cíclico das reservas existentes.
     */
    private function calcularStockMinimo($tabelaPivot, $colunaFk, $entidadeId, $totalFisico, Carbon $startDate, Carbon $endDate, array $estadosBloqueantes)
    {
        $reservasOcupantes = \DB::table($tabelaPivot)
            ->join('reserves', "{$tabelaPivot}.reserve_id", '=', 'reserves.id')
            ->where("{$tabelaPivot}.{$colunaFk}", $entidadeId)
            ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
            ->where('reserves.start_date', '<=', $endDate->format('Y-m-d'))
            ->where('reserves.end_date', '>=', $startDate->format('Y-m-d'))
            ->select('reserves.start_date', 'reserves.end_date', "{$tabelaPivot}.quantity", 'reserves.ciclica_id')
            ->get();

        $minimoDisponivel = $totalFisico;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dataStr = $date->format('Y-m-d');
            $dow = $date->dayOfWeek;
            $ocupadosHoje = 0;

            foreach ($reservasOcupantes as $r) {
                if ($dataStr >= $r->start_date && $dataStr <= $r->end_date) {
                    $ehContinua = ($r->ciclica_id === null || (int) $r->ciclica_id === 1);
                    if ($ehContinua || $dow === ((int) $r->ciclica_id - 2)) {
                        $ocupadosHoje += $r->quantity;
                    }
                }
            }

            $disponivelHoje = $totalFisico - $ocupadosHoje;
            if ($disponivelHoje < $minimoDisponivel) {
                $minimoDisponivel = $disponivelHoje;
            }
        }

        return max(0, $minimoDisponivel);
    }
}