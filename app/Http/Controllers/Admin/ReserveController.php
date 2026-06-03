<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserve;
use App\Models\Kit;
use App\Models\KitReserve;
use App\Models\Item;
use App\Models\ItemReserve;
use App\Models\CostCenter; // <-- Não te esqueças de importar o modelo do Centro de Custos
use Illuminate\Support\Facades\Auth;
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
        $reservas = Reserve::all();

        foreach ($reservas as $reserve) {
            if ($reserve->reserve_state_id == 7) {
                $todaydate = date('Y-m-d');
                if ($todaydate > $reserve->end_date) {
                    $reserve->reserve_state_id = 4;
                }
                $reserve->save();
            }
        }
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.delayed', ['reserves' => Reserve::where('reserve_state_id', '4')->orWhere('reserve_state_id', '9')->get()]);
        }
        return redirect('/');
    }

    public function ongoing()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.ongoing', ['reserves' => Reserve::where('reserve_state_id', '7')
                                                        ->orWhere('reserve_state_id', '8')->get()]);
        }
        return redirect('/');
        
    }

    public function unauthorized()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.unauthorized', ['reserves' => Reserve::where('reserve_state_id', '3')->get()]);
        }
        return redirect('/');
        
    }

    public function completed()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.completed', ['reserves' => Reserve::where('reserve_state_id', '5')->orWhere('reserve_state_id', '6')->get()]);
        }
        return redirect('/');
    }

    public function show($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.show', [
                'reserve' => Reserve::find($id),
                'reserve_kits' => KitReserve::where('reserve_id', $id)->get(),
                'kits' => Kit::all(),
                'reserve_itens' => ItemReserve::where('reserve_id', $id)->get(),
                'itens' => Item::all()
            ]);
        }
        return redirect('/');
        
    }

    public function PDFDownload($id)
    {
        $reserve = Reserve::find($id);
        $reserve_kits = KitReserve::where('reserve_id', $id)->get();
        $kits = Kit::all();
        $reserve_itens = ItemReserve::where('reserve_id', $id)->get();
        $itens = Item::all();
        $pdf = PDF::loadview('admin.reserves.PDF', compact('reserve', 'reserve_kits', 'kits', 'reserve_itens', 'itens'));

        return $pdf->download('Requisicao.pdf');
    }

    public function autorize($id)
    {
        $reserve = Reserve::find($id);
        
        // Verifica se a reserva estava pendente (para evitar faturar duas vezes se clicarem duas vezes no botão)
        if ($reserve->reserve_state_id == 1) {
            // Chamamos a nossa função mágica de cálculo
            $this->aplicarCustoReserva($reserve);
        }

        $reserve->reserve_state_id = 2;
        $reserve->save();

        return back();
    }

    public function decline($id)
    {
        $reserve = Reserve::find($id);
        $reserve->reserve_state_id = 3;
        $reserve->save();

        return back();
    }

    public function finalize($id)
    {
        $reserve = Reserve::find($id);
        $todaydate = date('Y-m-d');
        if($todaydate <= $reserve->end_date){
            $reserve->reserve_state_id = 5;
        }else{
            $reserve->reserve_state_id = 6;
        }
        $reserve->save();
        return back();
    }

    public function deliver($id)
    {
        $reserve = Reserve::find($id);
        $reserve->reserve_state_id = 7;
        $reserve->delivery_date = Carbon::now();
        $reserve->save();
        return back();
    }

    public function receive($id)
    {
        $reserve = Reserve::find($id);
        $reserve->return_date = Carbon::now();
        $todaydate = date('Y-m-d');

        if($todaydate <= $reserve->end_date){
            $reserve->reserve_state_id = 8;
        }else{
            $reserve->reserve_state_id = 9;
        }
        $reserve->save();
        return back();
    }


    /**
     * FUNÇÃO PRIVADA: Calcula os dias, preço total e atualiza o Centro de Custos
     */
    private function aplicarCustoReserva($reserve)
    {
        $start = Carbon::parse($reserve->start_date);
        $end = Carbon::parse($reserve->end_date);
        $numero_dias = 0;

        // 1. CALCULAR O NÚMERO DE DIAS (Lógica Normal vs Cíclica)
        if ($reserve->ciclica_id == 1 || $reserve->ciclica_id == null) {
            $numero_dias = $start->diffInDays($end);
            if ($numero_dias == 0) $numero_dias = 1; // Pelo menos 1 dia de taxa
        } else {
            // Reserva Cíclica: Conta apenas as ocorrências do dia da semana escolhido
            $diaSemanaAlvo = $reserve->ciclica_id - 2; 
            
            $numero_dias = $start->diffInDaysFiltered(function (Carbon $date) use ($diaSemanaAlvo) {
                return $date->dayOfWeek === $diaSemanaAlvo;
            }, $end);
            
            if ($end->dayOfWeek === $diaSemanaAlvo) {
                $numero_dias++;
            }
            if ($numero_dias == 0) $numero_dias = 1;
        }

        // 2. CALCULAR CUSTO TOTAL DOS EQUIPAMENTOS
        $custo_total_reserva = 0;

        // Somar os Itens
        $itemReserves = ItemReserve::where('reserve_id', $reserve->id)->get();
        foreach ($itemReserves as $ir) {
            $item = Item::find($ir->item_id);
            if ($item) {
                // Se o utilizador pediu 2 unidades do mesmo item, multiplica pela quantidade também
                $qtd = $ir->quantity ?? 1; 
                $custo_total_reserva += ($item->preco * $numero_dias * $qtd);
            }
        }

        // Somar os Kits
        $kitReserves = KitReserve::where('reserve_id', $reserve->id)->get();
        foreach ($kitReserves as $kr) {
            $kit = Kit::find($kr->kit_id);
            if ($kit) {
                $qtd = $kr->quantity ?? 1;
                $custo_total_reserva += ($kit->preco * $numero_dias * $qtd);
            }
        }


        // 1. ATUALIZAR A RESERVA COM O CUSTO FINAL CORRETO
        $reserve->cost = $custo_total_reserva;
        $reserve->save();

        // 2. ATUALIZAR O CENTRO DE CUSTOS
        if ($reserve->cost_center_id != null) {
            $centro = CostCenter::find($reserve->cost_center_id);
            if ($centro) {
                $centro->total_cost += $custo_total_reserva;
                $centro->total_debt += $custo_total_reserva; 
                $centro->save();
            }
        }
    }

    public function pay($id)
    {
        $reserve = Reserve::find($id);

        // Verifica se a reserva já tem custo (já foi autorizada) e se AINDA NÃO está paga
        if ($reserve->cost > 0 && !$reserve->is_paid) {
            
            // 1. Marca a reserva como paga
            $reserve->is_paid = true;
            $reserve->save();

            // 2. Subtrai o valor da reserva à dívida do Centro de Custos
            if ($reserve->cost_center_id != null) {
                $centro = CostCenter::find($reserve->cost_center_id);
                if ($centro) {
                    $centro->total_debt -= $reserve->cost;
                    
                    // Prevenção: garantir que a dívida nunca fica negativa por algum erro matemático
                    if ($centro->total_debt < 0) {
                        $centro->total_debt = 0;
                    }
                    $centro->save();
                }
            }
            
            return back()->with('toast_success', 'Reserva paga com sucesso! O valor foi subtraído ao Centro de Custos.');
        }

        return back()->with('warning', 'Esta reserva já se encontra paga ou não tem valor a cobrar.');
    }
}
