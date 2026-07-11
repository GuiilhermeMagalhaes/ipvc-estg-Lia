<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserve;
use App\Models\Kit;
use App\Models\KitReserve;
use App\Models\Item;
use App\Models\ItemReserve;
use App\Models\ItemUnity;
use App\Models\KitUnity;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class ReserveController extends Controller
{
    public function all()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.all', ['reserves' => Reserve::all()]);
        }
        return redirect('/');
    }

    public function pending()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.pending', ['reserves' => Reserve::where('reserve_state_id', 1)->get()]);
        }
        return redirect('/');
    }

    public function delayed()
    {
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        Reserve::where('reserve_state_id', 7)
               ->whereDate('end_date', '<', Carbon::today())
               ->update(['reserve_state_id' => 4]);

        
            return view('admin.reserves.delayed', [
                'reserves' => Reserve::whereIn('reserve_state_id', [4, 9])->get()
            ]);
        
    }

    public function ongoing()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.ongoing', [
                'reserves' => Reserve::whereIn('reserve_state_id', [7, 8])->get()
            ]);
        }
        return redirect('/');
    }

    public function unauthorized()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.unauthorized', ['reserves' => Reserve::where('reserve_state_id', 3)->get()]);
        }
        return redirect('/');
    }

    public function completed()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.completed', [
                'reserves' => Reserve::whereIn('reserve_state_id', [5, 6])->get()
            ]);
        }
        return redirect('/');
    }

    public function show($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {

            $reserve       = Reserve::findOrFail($id);
            $reserve_kits  = KitReserve::where('reserve_id', $id)->get();
            $reserve_itens = ItemReserve::where('reserve_id', $id)->get();

            // Reservas em curso (estados 4 e 7), excluindo a atual
            $reservasAtivasIds = Reserve::whereIn('reserve_state_id', [4, 7])
                ->where('id', '!=', $id)
                ->pluck('id');

            // (A) Kit unities entregues noutra reserva ativa
            $kitReservesAtivas = KitReserve::whereIn('reserve_id', $reservasAtivasIds)->pluck('id');
            $kitUnitiesOcupadas = DB::table('kit_unity_reserve')
                ->whereIn('kit_reserve_id', $kitReservesAtivas)
                ->pluck('kit_unity_id')->toArray();

            // (B) Item unities entregues individualmente noutra reserva ativa
            $itemReservesAtivas = ItemReserve::whereIn('reserve_id', $reservasAtivasIds)->pluck('id');
            $itemUnitiesSoltasOcupadas = DB::table('item_unity_reserve')
                ->whereIn('item_reserve_id', $itemReservesAtivas)
                ->pluck('item_unity_id')->toArray();

            // (C) Item unities que estão DENTRO de kits entregues noutra reserva ativa
            $itemUnitiesEmKitsOcupados = ItemUnity::whereIn('kit_unity_id', $kitUnitiesOcupadas)
                ->pluck('id')->toArray();

            // (D) Item unities que estão dentro dos kits pedidos NESTA reserva
            $kitIdsDestaReserva   = $reserve_kits->pluck('kit_id')->toArray();
            $kitUnitiesDestesKits = KitUnity::whereIn('kit_id', $kitIdsDestaReserva)->pluck('id')->toArray();
            $itemUnitiesDestaReservaKits = ItemUnity::whereIn('kit_unity_id', $kitUnitiesDestesKits)
                ->pluck('id')->toArray();

            // (E) PASSO 3: Kit units cujas peças internas já saíram individualmente noutra reserva ativa
            $kitUnitiesComPecaForaIndividual = ItemUnity::whereIn('id', $itemUnitiesSoltasOcupadas)
                ->whereNotNull('kit_unity_id')
                ->pluck('kit_unity_id')->toArray();

            // Kit units a esconder = entregues como kit + as que têm peça fora individualmente
            $kitUnitiesOcupadas = array_values(array_unique(array_merge(
                $kitUnitiesOcupadas,
                $kitUnitiesComPecaForaIndividual
            )));

            // Peças que NÃO podem aparecer no select individual
            $itemUnitiesIndisponiveis = array_values(array_unique(array_merge(
                $itemUnitiesSoltasOcupadas,
                $itemUnitiesEmKitsOcupados,
                $itemUnitiesDestaReservaKits
            )));

            return view('admin.reserves.show', [
                'reserve'                  => $reserve,
                'reserve_kits'             => $reserve_kits,
                'kits'                     => Kit::all(),
                'reserve_itens'            => $reserve_itens,
                'itens'                    => Item::all(),
                'itemUnitiesIndisponiveis' => $itemUnitiesIndisponiveis,
                'kitUnitiesOcupadas'       => $kitUnitiesOcupadas,
            ]);
        }
        return redirect('/');
    }

    public function PDFDownload($id)
    {
         if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $reserve       = Reserve::find($id);
        $reserve_kits  = KitReserve::where('reserve_id', $id)->get();
        $kits          = Kit::all();
        $reserve_itens = ItemReserve::where('reserve_id', $id)->get();
        $itens         = Item::all();

        $pdf = PDF::loadview('admin.reserves.PDF', compact('reserve', 'reserve_kits', 'kits', 'reserve_itens', 'itens'));

        return $pdf->download('Requisicao.pdf');
    }

    public function autorize($id)
    {
         if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }
        $reserve = Reserve::findOrFail($id);

        if ($reserve->reserve_state_id == 1) {
            $this->aplicarCustoReserva($reserve);
        }

        $reserve->reserve_state_id = 2;
        $reserve->save();

        return back()->with('toast_success', 'Reserva autorizada com sucesso!');
    }

    public function decline($id)
    {
         if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $reserve = Reserve::findOrFail($id);
        $reserve->reserve_state_id = 3;
        $reserve->save();

        return back()->with('toast_success', 'Reserva recusada.');
    }

 public function deliver(Request $request, $id) 
    {
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        // Junta todos os IDs dos itens individuais escolhidos
        $selectedItemUnities = [];
        if ($request->has('atribuicao')) {
            foreach ($request->atribuicao as $item_reserve_id => $unities) {
                $selectedItemUnities = array_merge($selectedItemUnities, $unities);
            }
        }

        // Junta todos os IDs das malas escolhidas
        $selectedKitUnities = [];
        if ($request->has('atribuicao_kit')) {
            foreach ($request->atribuicao_kit as $kit_reserve_id => $unities) {
                $selectedKitUnities = array_merge($selectedKitUnities, $unities);
            }
        }

        // Remove valores vazios (linhas deixadas em branco)
        $selectedItemUnities = array_values(array_filter($selectedItemUnities, fn($v) => !empty($v)));
        $selectedKitUnities  = array_values(array_filter($selectedKitUnities, fn($v) => !empty($v)));

        // Tem de haver pelo menos uma unidade
        if (empty($selectedItemUnities) && empty($selectedKitUnities)) {
            return back()->with('toast_error', 'Selecione pelo menos uma unidade para entregar.');
        }

        // 1. CONFLITO: peça individual que já está dentro de uma das malas selecionadas
        if (!empty($selectedItemUnities) && !empty($selectedKitUnities)) {
            $conflitoFisico = \App\Models\ItemUnity::whereIn('id', $selectedItemUnities)
                        ->whereIn('kit_unity_id', $selectedKitUnities)
                        ->exists();

            if ($conflitoFisico) {
                return redirect()->back()->with('toast_error', 'Erro de Atribuição: Está a tentar entregar uma peça individual que já se encontra dentro de uma das Malas selecionadas!');
            }
        }

        // 2. DUPLICADOS: a mesma unidade escolhida mais do que uma vez nesta reserva
        if (count($selectedItemUnities) !== count(array_unique($selectedItemUnities))) {
            return redirect()->back()->with('toast_error', 'Erro de Atribuição: selecionou o mesmo item mais do que uma vez nesta reserva.');
        }
        if (count($selectedKitUnities) !== count(array_unique($selectedKitUnities))) {
            return redirect()->back()->with('toast_error', 'Erro de Atribuição: selecionou a mesma mala (Kit) mais do que uma vez nesta reserva.');
        }

        // 3. JÁ ATRIBUÍDO: unidade já entregue noutra reserva em curso (estados 4 e 7)
        $reservasAtivasIds = Reserve::whereIn('reserve_state_id', [4, 7])
            ->where('id', '!=', $id)
            ->pluck('id');

        if (!empty($selectedItemUnities)) {
            $itemReservesAtivas = ItemReserve::whereIn('reserve_id', $reservasAtivasIds)->pluck('id');
            $itensOcupados = DB::table('item_unity_reserve')
                ->whereIn('item_reserve_id', $itemReservesAtivas)
                ->whereIn('item_unity_id', $selectedItemUnities)
                ->exists();

            if ($itensOcupados) {
                return redirect()->back()->with('toast_error', 'Erro de Atribuição: um dos itens selecionados já se encontra atribuído a outra reserva em curso.');
            }
        }

        if (!empty($selectedKitUnities)) {
            $kitReservesAtivas = KitReserve::whereIn('reserve_id', $reservasAtivasIds)->pluck('id');
            $kitsOcupados = DB::table('kit_unity_reserve')
                ->whereIn('kit_reserve_id', $kitReservesAtivas)
                ->whereIn('kit_unity_id', $selectedKitUnities)
                ->exists();

            if ($kitsOcupados) {
                return redirect()->back()->with('toast_error', 'Erro de Atribuição: uma das malas (Kit) selecionadas já se encontra atribuída a outra reserva em curso.');
            }
        }

        // Tudo validado -> grava
        DB::transaction(function () use ($request, $id) {

            if ($request->has('atribuicao')) {
                foreach ($request->atribuicao as $reserve_item_id => $unity_ids) {
                    foreach ($unity_ids as $unity_id) {
                        if (empty($unity_id)) continue;

                        DB::table('item_unity_reserve')->insert([
                            'item_reserve_id' => $reserve_item_id,
                            'item_unity_id'   => $unity_id
                        ]);
                    }
                }
            }

            if ($request->has('atribuicao_kit')) {
                foreach ($request->atribuicao_kit as $reserve_kit_id => $unity_ids) {
                    foreach ($unity_ids as $unity_id) {
                        if (empty($unity_id)) continue;

                        DB::table('kit_unity_reserve')->insert([
                            'kit_reserve_id' => $reserve_kit_id,
                            'kit_unity_id'   => $unity_id
                        ]);
                    }
                }
            }

            Reserve::find($id)->update([
                'reserve_state_id' => 7,
                'delivery_date' => Carbon::now()
            ]);
        });

        return back()->with('toast_success', 'Equipamentos entregues e atribuídos com sucesso!');
    }

    public function receive(Request $request, $id)
    {
         if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $reserve = Reserve::findOrFail($id);
        $reserve->return_date = Carbon::now();
        $temProblema = $request->filled('return_notes');
        
        // Texto que o técnico escreveu na devolução (se não escreveu nada, gera um texto automático)
        $textoAvaria = $temProblema ? $request->return_notes : 'Avaria registada na devolução da Reserva #' . $id;
        
        $brokenItems = $request->input('broken_items', []); 
        $brokenKits = $request->input('broken_kits', []); 

        $kitsIncompletos = []; 

        // 1. RECEBER ITENS SOLTOS
        $itemReserves = ItemReserve::where('reserve_id', $id)->pluck('id');
        $unidades_fisicas = DB::table('item_unity_reserve')->whereIn('item_reserve_id', $itemReserves)->get();

        foreach ($unidades_fisicas as $uf) {
            $unidade = ItemUnity::find($uf->item_unity_id);
            if ($unidade) {
                $isBroken = in_array($unidade->id, $brokenItems);
                
                $unidade->item_unity_state_id = $isBroken ? 4 : 1; 
                
                // NOVA LÓGICA: Atualiza as observações automaticamente
                if ($isBroken) {
                    $unidade->observacoes = $textoAvaria;
                } else {
                    $unidade->observacoes = null; // Se devolveu em bom estado, limpa observações antigas
                }

                $unidade->save();

                if ($isBroken && $unidade->kit_unity_id) {
                    $kitsIncompletos[] = $unidade->kit_unity_id;
                }
            }
        }

        // 2. RECEBER KITS
        $kitReserves = KitReserve::where('reserve_id', $id)->pluck('id');
        $kits_fisicos = DB::table('kit_unity_reserve')->whereIn('kit_reserve_id', $kitReserves)->get();

        foreach ($kits_fisicos as $kf) {
            $kit_unidade = KitUnity::find($kf->kit_unity_id);
            if ($kit_unidade) {
                $isKitBroken = in_array($kit_unidade->id, $brokenKits) || in_array($kit_unidade->id, $kitsIncompletos);
                
                $kit_unidade->kit_unity_state_id = $isKitBroken ? 4 : 1; 
                
                // NOVA LÓGICA: Atualiza as observações da Mala
                if ($isKitBroken) {
                    // Se o kit avariou todo, leva a nota do técnico. Se ficou incompleto devido a uma peça, gera um aviso.
                    $motivoKit = in_array($kit_unidade->id, $brokenKits) ? $textoAvaria : 'Mala incompleta/bloqueada devido a avaria de um item interno (Reserva #' . $id . ')';
                    $kit_unidade->observacoes = $motivoKit;
                } else {
                    $kit_unidade->observacoes = null;
                }

                $kit_unidade->save();

                // NOVA LÓGICA: Atualiza os itens lá dentro
                $estadoDasPecas = $isKitBroken ? 4 : 1;
                $obsDasPecas = $isKitBroken ? 'Bloqueado devido a avaria/falha no Kit (Reserva #' . $id . ')' : null;
                
                ItemUnity::where('kit_unity_id', $kit_unidade->id)->update([
                    'item_unity_state_id' => $estadoDasPecas,
                    'observacoes' => $obsDasPecas
                ]);
            }
        }

        // 3. REGISTAR A AVARIA NA RESERVA
        if ($temProblema) {
            $reserve->return_notes = $request->return_notes;
        }

        // 4. FINALIZAR ESTADO DA RESERVA
        $todaydate = Carbon::today();
        $endDate   = Carbon::parse($reserve->end_date);

        $reserve->reserve_state_id = $todaydate->lte($endDate) ? 8 : 9;
        $reserve->save();
        
        return back()->with('toast_success', 'Material recebido e estados sincronizados com sucesso!');
    }

    public function finalize($id)
    {
         if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        DB::transaction(function () use ($id) {
            $reserve = Reserve::findOrFail($id);

            $todaydate = Carbon::today();
            $endDate = Carbon::parse($reserve->end_date);
            
            // 5 = Concluída a Tempo | 6 = Concluída com Atraso
            $reserve->reserve_state_id = $todaydate->lte($endDate) ? 5 : 6;
            $reserve->save();
        });
        
        return back()->with('toast_success', 'Reserva finalizada com sucesso e processo fechado!');
    }

    public function pay($id)
    {
         if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $reserve = Reserve::findOrFail($id);

        if ($reserve->cost > 0 && !$reserve->is_paid) {

            $reserve->is_paid = true;
            $reserve->save();

            if ($reserve->cost_center_id) {
                $centro = CostCenter::find($reserve->cost_center_id);
                if ($centro) {
                    $centro->total_debt -= $reserve->cost;
                    if ($centro->total_debt < 0) {
                        $centro->total_debt = 0;
                    }
                    $centro->save();
                }
            }

            return back()->with('toast_success', 'Reserva marcada como paga! O valor foi subtraído ao Centro de Custos.');
        }

        return back()->with('warning', 'Esta reserva já se encontra paga ou não tem valor a cobrar.');
    }

    private function aplicarCustoReserva($reserve)
    {
        // GUARDAMOS O CUSTO ANTIGO ANTES DE ATUALIZAR
        $custo_antigo_reserva = $reserve->cost ?? 0;
        
        // ATRIBUÍMOS O VALOR DIRETO DO CUSTO ESTIMADO QUE JÁ ESTAVA GRAVADO
        $custo_total_reserva = $reserve->estimated_cost ?? 0; // <--- ALTERADO AQUI

        // ATUALIZAMOS A RESERVA COM O NOVO CUSTO
        $reserve->cost = $custo_total_reserva;
        $reserve->save();

        // ATUALIZAMOS O CENTRO DE CUSTOS (SE EXISTIR)
        if ($reserve->cost_center_id) {
            $centro = CostCenter::find($reserve->cost_center_id);
            if ($centro) {

                $centro->total_cost = ($centro->total_cost - $custo_antigo_reserva) + $custo_total_reserva;
                $centro->total_debt = ($centro->total_debt - $custo_antigo_reserva) + $custo_total_reserva;
                
                if ($centro->total_debt < 0) $centro->total_debt = 0;
                if ($centro->total_cost < 0) $centro->total_cost = 0;

                $centro->save();
            }
        }
    }

    public function cancel($id)
    {
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $reserve = Reserve::findOrFail($id);

        // Não deixar cancelar reservas já concluídas, devolvidas ou já canceladas
        if (in_array($reserve->reserve_state_id, [5, 6, 8, 9, 10])) {
            return back()->with('toast_error', 'Esta reserva já está concluída, devolvida ou cancelada — não pode ser cancelada.');
        }

        DB::transaction(function () use ($reserve) {

            // 1. Libertar unidades de ITENS atribuídas (remover da pivot)
            $itemReserveIds = ItemReserve::where('reserve_id', $reserve->id)->pluck('id');
            DB::table('item_unity_reserve')->whereIn('item_reserve_id', $itemReserveIds)->delete();

            // 2. Libertar unidades de KITS atribuídas
            $kitReserveIds = KitReserve::where('reserve_id', $reserve->id)->pluck('id');
            DB::table('kit_unity_reserve')->whereIn('kit_reserve_id', $kitReserveIds)->delete();

            // 3. Reverter o custo no Centro de Custos (se já tinha sido aplicado)
            if ($reserve->cost_center_id && $reserve->cost > 0) {
                $centro = CostCenter::find($reserve->cost_center_id);
                if ($centro) {
                    $centro->total_cost = max(0, $centro->total_cost - $reserve->cost);
                    if (!$reserve->is_paid) {
                        $centro->total_debt = max(0, $centro->total_debt - $reserve->cost);
                    }
                    $centro->save();
                }
            }

            // 4. Marcar como Cancelada
            $reserve->reserve_state_id = 10;
            $reserve->cost = 0;
            $reserve->save();
        });

        return redirect()->route('reserves.all')->with('toast_success', 'Reserva cancelada. As unidades atribuídas voltaram a ficar disponíveis.');
    }
    
}