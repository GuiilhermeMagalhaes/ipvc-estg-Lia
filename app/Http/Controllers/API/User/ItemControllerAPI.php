<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Kit;
use App\Models\ItemCategorie;
use Illuminate\Support\Facades\DB;


class ItemControllerAPI extends Controller
{
    public function index(Request $request)
    {

    $search = $request->query('search');
        try {
            $estadoDisponivelId = 1; // ID do estado "Disponível" na tabela item_unity_states
            $categoriaId = $request->query('categoria_id', 'Todos');
            
            // Capturar as datas enviadas pelo formato Date-First do telemóvel
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // IDs dos estados de reserva que bloqueiam stock (Ex: 1-Pendente, 2-Autorizada, 4-Em Atraso, 7-Entregue)
            // Ajusta estes IDs se na tua tabela reserve_states forem diferentes!
            $estadosBloqueantes = [1, 2, 4, 7];

            $kitsDestaque = [];
            $itensLista = [];
            $paginacao = [];

            // ==========================================
            // CENÁRIO 1: UTILIZADOR CLICOU NA TAB "KITS"
            // ==========================================
            if ($categoriaId === 'Kits') {
                $queryKits = Kit::has('kitUnities');


                if (!empty($search)) {
                    $queryKits->where('name', 'like', "%{$search}%");
                }

                $kitsPaginated = $queryKits->withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('kit_unity_state_id', $estadoDisponivelId);
                }])->paginate(10);

                // Mapeamos os Kits calculando a disponibilidade real por data
                $itensTratados = collect($kitsPaginated->items())->map(function ($kit) use ($startDate, $endDate, $estadosBloqueantes) {
                    $kit->nome = $kit->name; 
                    $kit->model = $kit->description; 
                    
                    $totalFisico = $kit->kit_unities_count;

                    // Se o telemóvel enviou datas, calculamos o stock ocupado
                    if ($startDate && $endDate) {
                        $quantidadeReservada = \DB::table('kit_reserve')
                            ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
                            ->where('kit_reserve.kit_id', $kit->id)
                            ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
                            ->where('reserves.start_date', '<=', $endDate)
                            ->where('reserves.end_date', '>=', $startDate)
                            ->sum('kit_reserve.quantity');

                        $totalFisico = max(0, $totalFisico - $quantidadeReservada);
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
            // ==========================================
            // CENÁRIO 2: "TODOS" OU OUTRA CATEGORIA
            // ==========================================
            else {
                // Destaques Horizontais (Kits)
                if ($request->query('page', 1) == 1 && $categoriaId === 'Todos' && empty($search)) {
                    $kitsDestaqueRaw = Kit::has('kitUnities')
                        ->withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                            $query->where('kit_unity_state_id', $estadoDisponivelId);
                        }])->get();

                    // Filtrar stock dos destaques por data
                    $kitsDestaque = collect($kitsDestaqueRaw)->map(function ($kit) use ($startDate, $endDate, $estadosBloqueantes) {
                        $totalFisico = $kit->kit_unities_count;

                        if ($startDate && $endDate) {
                            $quantidadeReservada = \DB::table('kit_reserve')
                                ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
                                ->where('kit_reserve.kit_id', $kit->id)
                                ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
                                ->where('reserves.start_date', '<=', $endDate)
                                ->where('reserves.end_date', '>=', $startDate)
                                ->sum('kit_reserve.quantity');

                            $totalFisico = max(0, $totalFisico - $quantidadeReservada);
                        }
                        $kit->kit_unities_count = $totalFisico;
                        return $kit;
                    });
                }

       // Equipamentos Individuais
                $queryItens = Item::has('itemUnities');

                // 1. Aplica o filtro de pesquisa se existir
                if (!empty($search)) {
                    $queryItens->where(function ($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                    });
                }

                // 2. Aplica o comCount logo a seguir na instância da query
                $queryItens->withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('item_unity_state_id', $estadoDisponivelId);
                }]);

                // 3. Aplica a categoria se não for "Todos"
                if ($categoriaId !== 'Todos') {
                    $queryItens->where('categoria_id', $categoriaId);
                }

                // 4. Faz a paginação final
                $itensPaginated = $queryItens->paginate(10);
                
                // Mapeamos os equipamentos individuais para subtrair reservas concorrentes
                $itensLista = collect($itensPaginated->items())->map(function ($item) use ($startDate, $endDate, $estadosBloqueantes) {
                    $totalFisico = $item->item_unities_count;

                    if ($startDate && $endDate) {
                        $quantidadeReservada = \DB::table('item_reserve')
                            ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
                            ->where('item_reserve.item_id', $item->id)
                            ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
                            ->where('reserves.start_date', '<=', $endDate)
                            ->where('reserves.end_date', '>=', $startDate)
                            ->sum('item_reserve.quantity');

                        $totalFisico = max(0, $totalFisico - $quantidadeReservada);
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

            // Categorias para os botões do topo
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
            $estadoDisponivelId = 1; // Disponível
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $estadosBloqueantes = [1, 2, 4, 7]; // Pendente, Autorizada, Em Atraso, Entregue

            // 1. Procurar no Item
            $item = Item::with(['itemCategorie' => function ($query) {
                $query->select('id', 'description');
            }])
                ->withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('item_unity_state_id', $estadoDisponivelId);
                }])
                ->find($id);

            if ($item) {
                $totalFisico = $item->item_unities_count;

                // MATEMÁTICA DE DISPONIBILIDADE:
                if ($startDate && $endDate) {
                    $quantidadeReservada = \DB::table('item_reserve')
                        ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
                        ->where('item_reserve.item_id', $item->id)
                        ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
                        ->where('reserves.start_date', '<=', $endDate)
                        ->where('reserves.end_date', '>=', $startDate)
                        ->sum('item_reserve.quantity');

                    $totalFisico = max(0, $totalFisico - $quantidadeReservada);
                }

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id'           => $item->id,
                        'nome'         => $item->nome,
                        'model'        => $item->model,
                        'categoria'    => $item->itemCategorie ? $item->itemCategorie->description : 'Geral',
                        'observacao'   => $item->observation ?? 'Nenhuma observação',
                        'acessorios'   => $item->acessorio ?? 'Nenhum acessório listado',
                        'quantidade'   => $totalFisico, // Aqui enviamos a quantidade REAL calculada
                        'preco'        => $item->price_day ?? $item->preco ?? '20,00', 
                        'image'        => $item->image,
                        'is_kit'       => false
                    ]
                ], 200);
            }

            // 2. Se não encontrou no Item, vamos procurar na tabela de Kits
            $kit = Kit::withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('kit_unity_state_id', $estadoDisponivelId);
                }])
                ->find($id);

            if ($kit) {
                $totalFisicoKit = $kit->kit_unities_count;

                // MATEMÁTICA DE DISPONIBILIDADE (KITS):
                if ($startDate && $endDate) {
                    $quantidadeReservada = \DB::table('kit_reserve')
                        ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
                        ->where('kit_reserve.kit_id', $kit->id)
                        ->whereIn('reserves.reserve_state_id', $estadosBloqueantes)
                        ->where('reserves.start_date', '<=', $endDate)
                        ->where('reserves.end_date', '>=', $startDate)
                        ->sum('kit_reserve.quantity');

                    $totalFisicoKit = max(0, $totalFisicoKit - $quantidadeReservada);
                }

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id'           => $kit->id,
                        'nome'         => $kit->name, 
                        'model'        => $kit->description, 
                        'categoria'    => 'Kit Completo',
                        'observacao'   => 'Conjunto de Equipamentos',
                        'acessorios'   => 'Acessórios integrados no kit',
                        'quantidade'   => $totalFisicoKit, // Aqui enviamos a quantidade REAL calculada
                        'preco'        => $kit->price_day ?? $kit->price ?? '35,00',
                        'image'        => $kit->image,
                        'is_kit'       => true
                    ]
                ], 200);
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
}