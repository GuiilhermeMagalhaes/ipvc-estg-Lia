<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserve;
use App\Models\ItemReserve;
use App\Models\KitReserve;
use App\Models\Item;
use App\Models\Kit;
use App\Models\KitUnity;
use App\Models\User;
use App\Notifications\PedidoRequisicao;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ReserveControllerAPI extends Controller
{

    public function index(Request $request)
    {
        try {
            
            $user = $request->user();

            
            $reservas = $user->reserves()->
                with(['reserveState', 'itemReserves.item', 'kitReserves.kit'])
                ->orderBy('created_at', 'desc') // Mais recentes primeiro
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $reservas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar o histórico: ' . $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        // 1. Validação dos dados recebidos do telemóvel
        $request->validate([
            'description'    => 'required|string',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'cost_center_id' => 'required|integer',
            'ciclica_id'     => 'required|integer',
            'items'          => 'nullable|array',
            'items.*.id'     => 'required_with:items|integer',
            'items.*.quantity'=> 'required_with:items|integer|min:1',
            'kits'           => 'nullable|array',
            'kits.*.id'      => 'required_with:kits|integer',
            'kits.*.quantity' => 'required_with:kits|integer|min:1',
        ]);

        $user = Auth::user();
        if ($user->user_type_id == 6) {
            return response()->json([
                'status' => 'error',
                'message' => 'Atualize o seu perfil antes de criar uma reserva!'
            ], 403);
        }

        $items = $request->input('items', []);
        $kits = $request->input('kits', []);

        if (empty($items) && empty($kits)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Adicione pelo menos um kit ou um item para poder concluir!'
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $ciclicaId = (int) $request->ciclica_id;

        // Gerar período de datas para validação de stock
        $periodoDatas = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            if ($ciclicaId === 1 || $date->dayOfWeek === ($ciclicaId - 2)) {
                $periodoDatas[] = ['data' => $date->format('Y-m-d'), 'dayOfWeek' => $date->dayOfWeek];
            }
        }

        // ==========================================
        // VALIDAÇÃO DE STOCK DE ITENS
        // ==========================================
        foreach ($items as $itemData) {
            $item = Item::find($itemData['id']);
            if (!$item) {
                return response()->json(['status' => 'error', 'message' => "Item ID {$itemData['id']} não encontrado."], 44
        );
            }

            $totalFisico = DB::table('item_unity')->where('item_id', $item->id)->where('item_unity_state_id', 1)->count();
            
            $reservasOcupantes = DB::table('item_reserve')
                ->join('reserves', 'item_reserve.reserve_id', '=', 'reserves.id')
                ->where('item_reserve.item_id', $item->id)
                ->whereIn('reserves.reserve_state_id', [1, 2, 7])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('reserves.start_date', [$startDate, $endDate])
                      ->orWhereBetween('reserves.end_date', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->where('reserves.start_date', '<=', $startDate)->where('reserves.end_date', '>=', $endDate);
                      });
                })->select('reserves.start_date', 'reserves.end_date', 'item_reserve.quantity', 'reserves.ciclica_id')->get();

            $minimoDisponivel = $totalFisico;
            foreach ($periodoDatas as $pData) {
                $ocupadosHoje = 0;
                foreach ($reservasOcupantes as $reserva) {
                    if ($pData['data'] >= $reserva->start_date && $pData['data'] <= $reserva->end_date) {
                        if ((int)$reserva->ciclica_id === 1 || $pData['dayOfWeek'] === ((int)$reserva->ciclica_id - 2)) {
                            $ocupadosHoje += $reserva->quantity;
                        }
                    }
                }
                $disponivelHoje = $totalFisico - $ocupadosHoje;
                if ($disponivelHoje < $minimoDisponivel) $minimoDisponivel = $disponivelHoje;
            }

            if ($itemData['quantity'] > $minimoDisponivel) {
                return response()->json([
                    'status' => 'error',
                    'message' => "O item '{$item->nome}' não tem stock suficiente. Máximo disponível: {$minimoDisponivel}"
                ], 422);
            }
        }

        // ==========================================
        // VALIDAÇÃO DE STOCK DE KITS
        // ==========================================
        foreach ($kits as $kitData) {
            $kit = Kit::find($kitData['id']);
            if (!$kit) {
                return response()->json(['status' => 'error', 'message' => "Kit ID {$kitData['id']} não encontrado."], 44);
            }

            $totalFisico = KitUnity::where('kit_id', $kit->id)->where('kit_unity_state_id', 1)->count();

            $reservasOcupantes = DB::table('kit_reserve')
                ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
                ->where('kit_reserve.kit_id', $kit->id)
                ->whereIn('reserves.reserve_state_id', [1, 2, 7])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('reserves.start_date', [$startDate, $endDate])
                      ->orWhereBetween('reserves.end_date', [$startDate, $endDate])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->where('reserves.start_date', '<=', $startDate)->where('reserves.end_date', '>=', $endDate);
                      });
                })->select('reserves.start_date', 'reserves.end_date', 'kit_reserve.quantity', 'reserves.ciclica_id')->get();

            $minimoDisponivel = $totalFisico;
            foreach ($periodoDatas as $pData) {
                $ocupadosHoje = 0;
                foreach ($reservasOcupantes as $reserva) {
                    if ($pData['data'] >= $reserva->start_date && $pData['data'] <= $reserva->end_date) {
                        if ((int)$reserva->ciclica_id === 1 || $pData['dayOfWeek'] === ((int)$reserva->ciclica_id - 2)) {
                            $ocupadosHoje += $reserva->quantity;
                        }
                    }
                }
                $disponivelHoje = $totalFisico - $ocupadosHoje;
                if ($disponivelHoje < $minimoDisponivel) $minimoDisponivel = $disponivelHoje;
            }

            if ($kitData['quantity'] > $minimoDisponivel) {
                return response()->json([
                    'status' => 'error',
                    'message' => "O kit '{$kit->name}' não tem stock suficiente. Máximo disponível: {$minimoDisponivel}"
                ], 422);
            }
        }

        // ==========================================
        // SALVAR RESERVA (TRANSAÇÃO DB)
        // ==========================================
        DB::beginTransaction();
        try {
            $reserve = Reserve::create([
                'description'      => $request->description,
                'cost_center_id'   => $request->cost_center_id,
                'ciclica_id'       => $request->ciclica_id,
                'user_id'          => $user->id,
                'start_date'       => $request->start_date,
                'end_date'         => $request->end_date,
                'cost'             => 0,
                'reserve_state_id' => 1,
                'delivery_date'    => null,
                'return_date'      => null  
            ]);

            foreach ($kits as $kitData) {
                KitReserve::create([
                    'reserve_id' => $reserve->id,
                    'kit_id'     => $kitData['id'],
                    'quantity'   => $kitData['quantity']
                ]);
            }

            foreach ($items as $itemData) {
                ItemReserve::create([
                    'reserve_id' => $reserve->id,
                    'item_id'    => $itemData['id'],
                    'quantity'   => $itemData['quantity']
                ]);
            }

            DB::commit();

            // Notificação aos Gestores
            $gestores = User::where('user_type_id', 1)->get(); 
            if ($gestores->isNotEmpty()) {
                Notification::send($gestores, new PedidoRequisicao($reserve));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Reserva efetuada com sucesso!'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao salvar reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
{
    try {
        // Buscamos a reserva com os itens, kits e o estado associados
        $reserva = \App\Models\Reserve::with([
            'itemReserves.item', 
            'kitReserves.kit', 
            'reserveState'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $reserva
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Reserva não encontrada ou erro ao carregar dados.'
        ], 404);
    }
}

public function cancel(Request $request, $id)
    {
        try {
            $reserva = \App\Models\Reserve::findOrFail($id);

            // 1. Segurança: Garantir que o utilizador só cancela as SUAS próprias reservas
            if ($reserva->user_id !== $request->user()->id) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Não tens permissão para cancelar esta reserva.'
                ], 403);
            }

            // 2. Regra de Negócio: Só se pode cancelar se ainda estiver "Pendente" (Estado 1)
            if ($reserva->reserve_state_id !== 1) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Apenas reservas pendentes podem ser canceladas.'
                ], 400);
            }

            $reserva->reserve_state_id = 10;
            $reserva->save();

            return response()->json([
                'status' => 'success', 
                'message' => 'A reserva foi cancelada com sucesso.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao tentar cancelar a reserva: ' . $e->getMessage()
            ], 500);
        }
    }


}