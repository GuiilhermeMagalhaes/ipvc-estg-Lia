<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Kit;
use App\Models\ItemCategorie;

class ItemControllerAPI extends Controller
{
    public function index(Request $request)
    {
        try {
            $estadoDisponivelId = 1; 
            $categoriaId = $request->query('categoria_id', 'Todos');

            $kitsDestaque = [];
            $itensLista = [];
            $paginacao = [];

            // ==========================================
            // CENÁRIO 1: UTILIZADOR CLICOU NA TAB "KITS"
            // ==========================================
            if ($categoriaId === 'Kits') {
                $kitsPaginated = Kit::has('kitUnities')
                    ->withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                        $query->where('kit_unity_state_id', $estadoDisponivelId);
                    }])->paginate(10);

                // Mapeamos (transformamos) as variáveis do Kit para fingirem ser Itens 
                // para que a FlatList do telemóvel não precise de ser alterada!
                $itensTratados = collect($kitsPaginated->items())->map(function ($kit) {
                    $kit->nome = $kit->name; // name vira nome
                    $kit->model = $kit->description; // description vira model
                    $kit->item_unities_count = $kit->kit_unities_count;
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
            // CENÁRIO 2: UTILIZADOR CLICOU EM "TODOS" OU OUTRA CATEGORIA
            // ==========================================
            else {
                // Só enviamos os Destaques horizontais se for a aba "Todos" e estivermos na pág 1
                if ($request->query('page', 1) == 1 && $categoriaId === 'Todos') {
                    $kitsDestaque = Kit::has('kitUnities')
                        ->withCount(['kitUnities' => function ($query) use ($estadoDisponivelId) {
                            $query->where('kit_unity_state_id', $estadoDisponivelId);
                        }])->get();
                }

                $queryItens = Item::has('itemUnities')
                    ->withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                        $query->where('item_unity_state_id', $estadoDisponivelId);
                    }]);

                if ($categoriaId !== 'Todos') {
                    $queryItens->where('categoria_id', $categoriaId);
                }

                $itensPaginated = $queryItens->paginate(10);
                $itensLista = $itensPaginated->items();
                $paginacao = [
                    'pagina_atual' => $itensPaginated->currentPage(),
                    'ultima_pagina' => $itensPaginated->lastPage(),
                    'tem_mais' => $itensPaginated->hasMorePages()
                ];
            }

            // ==========================================
            // COMUM: CARREGAR AS CATEGORIAS DOS BOTÕES (SÓ PÁG 1)
            // ==========================================
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


//  RETORNAR DETALHES DE UM ITEM OU KIT

    public function show($id)
    {
        try {
            $estadoDisponivelId = 1; // Disponível

            // 1. Procurar no Item (Usando o nome correto da função de relação: itemCategorie)
            $item = Item::with(['itemCategorie' => function ($query) {
                $query->select('id', 'description');
            }])
                ->withCount(['itemUnities' => function ($query) use ($estadoDisponivelId) {
                    $query->where('item_unity_state_id', $estadoDisponivelId);
                }])
                ->find($id);

            if ($item) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id'           => $item->id,
                        'nome'         => $item->nome,
                        'model'        => $item->model,
                        // Chamar o relacionamento correto: itemCategorie
                        'categoria'    => $item->itemCategorie ? $item->itemCategorie->description : 'Geral',
                        // Colunas com o nome exato da base de dados (observation e acessorio)
                        'observacao'   => $item->observation ?? 'Nenhuma observação',
                        'acessorios'   => $item->acessorio ?? 'Nenhum acessório listado',
                        'quantidade'   => $item->item_unities_count,
                        // Verifica se existe price_day, senão usa preco
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
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id'           => $kit->id,
                        'nome'         => $kit->name, 
                        'model'        => $kit->description, 
                        'categoria'    => 'Kit Completo',
                        'observacao'   => 'Conjunto de Equipamentos',
                        'acessorios'   => 'Acessórios integrados no kit',
                        'quantidade'   => $kit->kit_unities_count,
                        // A coluna no Model Kit é price_day ou price
                        'preco'        => $kit->price_day ?? $kit->price ?? '35,00',
                        'image'        => $kit->image,
                        'is_kit'       => true
                    ]
                ], 200);
            }

            // 3. Se não encontrou em nenhum dos lados
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