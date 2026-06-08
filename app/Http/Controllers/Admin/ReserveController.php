<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserve;
use App\Models\Kit;
use App\Models\KitReserve;
use App\Models\Item;
use App\Models\ItemReserve;
use App\Models\ItemUnity;
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
        $request->validate([
            'atribuicao' => 'required|array',
        ]);

        DB::transaction(function () use ($request, $id) {
            foreach ($request->atribuicao as $reserve_item_id => $unity_id) {

                // CORRIGIDO: usar where()->update() em vez de find()->update()
                // para garantir que o Eloquent persiste corretamente na BD
                ItemReserve::where('id', $reserve_item_id)
                    ->update(['item_unity_id' => $unity_id]);

                ItemUnity::where('id', $unity_id)
                    ->update(['item_unity_state_id' => 2]); // 2 = Ocupada
            }

            Reserve::where('id', $id)->update([
                'reserve_state_id' => 4, // Entregue
                'delivery_date'    => Carbon::now(),
            ]);
        });

        return back()->with('toast_success', 'Material entregue e unidades atribuídas com sucesso!');
    }

    public function receive(Request $request, $id)
    {
        $reserve = Reserve::findOrFail($id);
        $reserve->return_date = Carbon::now();

        $temProblema = $request->filled('return_notes');

        $unidades_da_reserva = ItemReserve::where('reserve_id', $id)->get();

        foreach ($unidades_da_reserva as $ur) {
            if ($ur->item_unity_id) {
                ItemUnity::where('id', $ur->item_unity_id)->update([
                    // Com problema -> estado 2 (Manutenção); sem problema -> estado 1 (Disponível)
                    'item_unity_state_id' => $temProblema ? 2 : 1,
                ]);
            }
        }

        if ($temProblema) {
            $reserve->return_notes = $request->return_notes;
        }

        $todaydate = Carbon::today();
        $endDate   = Carbon::parse($reserve->end_date);

        // 8 = Devolvida a tempo; 9 = Devolvida com atraso
        $reserve->reserve_state_id = $todaydate->lte($endDate) ? 8 : 9;
        $reserve->save();

        return back()->with('toast_success', 'Material recebido e stock atualizado!');
    }

    public function finalize($id)
{
    DB::transaction(function () use ($id) {
        $reserve = Reserve::findOrFail($id);

        $itensReserva = ItemReserve::where('reserve_id', $id)->get();
        foreach ($itensReserva as $ri) {
            if ($ri->item_unity_id) {
                // Só volta a visível se a reserva não tiver problemas
                $novoEstado = $reserve->return_notes ? 2 : 1;
                ItemUnity::where('id', $ri->item_unity_id)
                    ->update(['item_unity_state_id' => $novoEstado]);
            }
        }

        $todaydate = Carbon::today();
        $endDate = Carbon::parse($reserve->end_date);
        $reserve->reserve_state_id = $todaydate->lte($endDate) ? 5 : 6;
        $reserve->save();
    });

    return back()->with('toast_success', 'Reserva finalizada e unidades libertadas.');
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

    // -------------------------------------------------------------------------
    // MÉTODO PRIVADO: Calcula e aplica o custo da reserva ao Centro de Custos
    // -------------------------------------------------------------------------
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

        $reserve->cost = $custo_total_reserva;
        $reserve->save();

        if ($reserve->cost_center_id) {
            $centro = CostCenter::find($reserve->cost_center_id);
            if ($centro) {
                $centro->total_cost += $custo_total_reserva;
                $centro->total_debt += $custo_total_reserva;
                $centro->save();
            }
        }
    }
}