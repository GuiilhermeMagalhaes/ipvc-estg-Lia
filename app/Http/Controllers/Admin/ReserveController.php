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
        Reserve::where('reserve_state_id', 7)
               ->whereDate('end_date', '<', Carbon::today())
               ->update(['reserve_state_id' => 4]);

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.delayed', [
                'reserves' => Reserve::whereIn('reserve_state_id', [4, 9])->get()
            ]);
        }
        return redirect('/');
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
            return view('admin.reserves.show', [
                'reserve'       => Reserve::findOrFail($id),
                'reserve_kits'  => KitReserve::where('reserve_id', $id)->get(),
                'kits'          => Kit::all(),
                'reserve_itens' => ItemReserve::where('reserve_id', $id)->get(),
                'itens'         => Item::all(),
            ]);
        }
        return redirect('/');
    }

    public function PDFDownload($id)
    {
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
        $reserve = Reserve::findOrFail($id);
        $reserve->reserve_state_id = 3;
        $reserve->save();

        return back()->with('toast_success', 'Reserva recusada.');
    }

    public function deliver(Request $request, $id) 
    {
        if (!$request->has('atribuicao') && (!$request->has('atribuicao_kit'))) {
            return back()->with('toast_error', 'Selecione pelo menos uma unidade para entregar.');
        }

        DB::transaction(function () use ($request, $id) {
            
            if ($request->has('atribuicao')) {
                foreach ($request->atribuicao as $reserve_item_id => $unity_ids) {
                    foreach ($unity_ids as $unity_id) {
                        if (empty($unity_id)) continue;

                        DB::table('item_unity_reserve')->insert([
                            'item_reserve_id' => $reserve_item_id,
                            'item_unity_id'   => $unity_id
                        ]);

                        ItemUnity::find($unity_id)->update([
                            'item_unity_state_id' => 2 
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

                        KitUnity::find($unity_id)->update([
                            'kit_unity_state_id' => 2 
                        ]);
                    }
                }
            }

            Reserve::find($id)->update([
                'reserve_state_id' => 4, 
                'delivery_date' => Carbon::now()
            ]);
        });

        return back()->with('toast_success', 'Equipamentos entregues e atribuídos com sucesso!');
    }

    public function receive(Request $request, $id)
    {
        $reserve = Reserve::findOrFail($id);
        $reserve->return_date = Carbon::now();
        $temProblema = $request->filled('return_notes');
        
        // Vamos buscar os arrays com os IDs que o admin selecionou na Modal (se não houver, fica array vazio)
        $brokenItems = $request->input('broken_items', []); 
        $brokenKits = $request->input('broken_kits', []); 

        $kitsIncompletos = []; // Para guardar que kits ficam incompletos devido a uma peça avariada

        // 1. RECEBER ITENS SOLTOS
        $itemReserves = ItemReserve::where('reserve_id', $id)->pluck('id');
        $unidades_fisicas = DB::table('item_unity_reserve')->whereIn('item_reserve_id', $itemReserves)->get();

        foreach ($unidades_fisicas as $uf) {
            $unidade = ItemUnity::find($uf->item_unity_id);
            if ($unidade) {
                // VERIFICAÇÃO PRECISA: Este LIA específico foi selecionado na modal?
                $isBroken = in_array($unidade->id, $brokenItems);
                
                $unidade->item_unity_state_id = $isBroken ? 4 : 1; 
                $unidade->save();

                // Lógica de Segurança: Se este item partiu e faz parte de uma mala/kit, a mala tem de ser bloqueada
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
                // VERIFICAÇÃO PRECISA: A mala avariou? OU falta-lhe alguma peça que avariou no passo acima?
                $isKitBroken = in_array($kit_unidade->id, $brokenKits) || in_array($kit_unidade->id, $kitsIncompletos);
                
                $kit_unidade->kit_unity_state_id = $isKitBroken ? 4 : 1; 
                $kit_unidade->save();
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
        
        return back()->with('toast_success', 'Material recebido! Avarias isoladas com precisão cirúrgica.');
    }

    public function finalize($id)
    {
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
        $start = Carbon::parse($reserve->start_date);
        $end   = Carbon::parse($reserve->end_date);
        $numero_dias = 0;

        if ($reserve->ciclica_id == 1 || $reserve->ciclica_id == null) {
            $numero_dias = $start->diffInDays($end);
            if ($numero_dias == 0) $numero_dias = 1;
        } else {
            $diaSemanaAlvo = $reserve->ciclica_id - 2;

            $numero_dias = $start->diffInDaysFiltered(function (Carbon $date) use ($diaSemanaAlvo) {
                return $date->dayOfWeek === $diaSemanaAlvo;
            }, $end);

            if ($end->dayOfWeek === $diaSemanaAlvo) {
                $numero_dias++;
            }
            if ($numero_dias == 0) $numero_dias = 1;
        }

        // GUARDAMOS O CUSTO ANTIGO ANTES DE FAZER NOVAS CONTAS
        $custo_antigo_reserva = $reserve->cost ?? 0;
        
        $custo_total_reserva = 0;

        $itemReserves = ItemReserve::where('reserve_id', $reserve->id)->get();
        foreach ($itemReserves as $ir) {
            $item = Item::find($ir->item_id);
            if ($item) {
                $qtd = $ir->quantity ?? 1;
                $custo_total_reserva += ($item->price_day * $numero_dias * $qtd);
            }
        }

        $kitReserves = KitReserve::where('reserve_id', $reserve->id)->get();
        foreach ($kitReserves as $kr) {
            $kit = Kit::find($kr->kit_id);
            if ($kit) {
                $qtd = $kr->quantity ?? 1;
                $custo_total_reserva += ($kit->price_day * $numero_dias * $qtd);
            }
        }

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
}