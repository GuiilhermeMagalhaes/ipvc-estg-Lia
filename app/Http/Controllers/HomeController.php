<?php

namespace App\Http\Controllers;

use App\Models\ItemCategorie;
use App\Models\Reserve;
use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\Kit;
use App\Models\KitReserve;
use App\Models\ItemReserve;
use App\Models\SpaceReserve;
use Carbon\Carbon;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;



class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
 public function index()
    {
        $reservas = Reserve::all();

        foreach ($reservas as $reserve) {
            if ($reserve->id == 7) {
                $todaydate = date('Y-m-d');
                if ($todaydate > $reserve->end_date) {
                    $reserve->reserve_state_id = 4;
                }
                $reserve->save();
            }
        }

        session()->put('categories', ItemCategorie::all());
        return view('layouts.menu', ['categories' => ItemCategorie::all()]);
    }

   public function adminIndex()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            $centros = CostCenter::all();
            $totalCost = $centros->sum('total_cost');
            $totalDebt = $centros->sum('total_debt');
            $totalUsers = User::all()->count();
            $reserves = Reserve::all();

            $ongoingReserves = $reserves->filter(fn($r) => $r->reserveState->id == 7 || $r->reserveState->id == 8);
            $ongoingCount = $ongoingReserves->count();
            $pendingCount = $reserves->filter(fn($r) => $r->reserveState->id == 1)->count();
            $delayedReserves = $reserves->filter(fn($r) => $r->reserveState->id == 4);

            // 1. GRÁFICO CENTROS DE CUSTO
            $stats = Reserve::select('cost_center_id', DB::raw('count(*) as total'))
                ->groupBy('cost_center_id')
                ->with('costCenter')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
            $labels = $stats->map(fn($s) => $s->costCenter->name ?? 'Sem Centro')->toArray();
            $values = $stats->pluck('total')->toArray();

            // 2. TOP 10 EQUIPAMENTOS (QUANTIDADE)
            $topItens = ItemReserve::with(['item', 'reserve'])->get()->groupBy('item_id')->map(fn($g) => [
                'nome' => $g->first()->item->nome ?? 'Item Desconhecido',
                'total' => $g->count()
            ]);
            $topKits = KitReserve::with(['kit', 'reserve'])->get()->groupBy('kit_id')->map(fn($g) => [
                'nome' => ($g->first()->kit->name ?? 'Kit Desconhecido') . ' (Kit)',
                'total' => $g->count()
            ]);
            $topEquipamentos = $topItens->concat($topKits)->sortByDesc('total')->take(10)->values();
            $topNomes = $topEquipamentos->pluck('nome')->toArray();
            $topValores = $topEquipamentos->pluck('total')->toArray();

            // 3. MATERIAIS MAIS LUCRATIVOS (VALOR)
            $calcLucro = function($g, $tipo) {
                $total = 0;
                foreach ($g as $pivot) {
                    $res = $pivot->reserve;
                    if ($res && $res->start_date && $res->end_date) {
                        $dias = max(1, Carbon::parse($res->start_date)->diffInDays(Carbon::parse($res->end_date)));
                        $preco = ($tipo == 'item' ? ($pivot->item->price_day ?? 0) : ($pivot->kit->price_day ?? 0));
                        $total += ($dias * $preco);
                    }
                }
                return $total;
            };

            $lucroItens = ItemReserve::with(['item', 'reserve'])->get()->groupBy('item_id')->map(fn($g) => [
                'nome' => $g->first()->item->nome ?? 'Item Desconhecido',
                'dinheiro_gerado' => $calcLucro($g, 'item')
            ]);
            $lucroKits = KitReserve::with(['kit', 'reserve'])->get()->groupBy('kit_id')->map(fn($g) => [
                'nome' => ($g->first()->kit->name ?? 'Kit Desconhecido') . ' (Kit)',
                'dinheiro_gerado' => $calcLucro($g, 'kit')
            ]);

            $topDinheiro = $lucroItens->concat($lucroKits)->sortByDesc('dinheiro_gerado')->take(5)->values();

            return view('layouts.admin-home', compact(
                'reserves', 'ongoingReserves', 'ongoingCount', 'pendingCount', 'delayedReserves', 
                'totalCost', 'totalDebt', 'totalUsers', 'labels', 'values', 'topNomes', 'topValores', 'topDinheiro'
            ));
        }
        return redirect('/');
    }

    public function PDFItensDisp()
    {
        // Recupera os itens com estado 1 e ordena por nome
        $itens = Item::where('item_state_id', '=', 1)
            ->orderBy('nome', 'asc') // Adiciona a ordenação por nome em ordem ascendente
            ->get();
        // Carrega a visualização do PDF com os itens
        $pdf = PDF::loadview('admin.reports.PDFitensdisp', compact('itens'));
        // Retorna o download do PDF gerado
        return $pdf->download('RelatorioEquipamentosDisponiveis.pdf');
    }


    public function PDFItensInd()
    {
        $itens = Item::where('item_state_id', '=', 2)
            ->orderBy('nome', 'asc')
            ->get();
        $pdf = PDF::loadview('admin.reports.PDFitensind', compact('itens'));
        return $pdf->download('RelatorioEquipamentosIndisponiveis.pdf');
    }

    public function ExcelRes(Request $request)
    {
        // Obter as datas de início e fim dos inputs
        $dataInicio = $request->input('dataInicio');
        $dataFim = $request->input('dataFim');

        // Verificar se as datas foram fornecidas
        if ($dataInicio == null || $dataFim == null) {
            return redirect()->back()->with('toast_error', 'Por favor, selecione as datas de início e fim!');
        }

        // Filtrar as reservas baseado nas datas
        $reservas = Reserve::whereBetween('start_date', [$dataInicio, $dataFim])
            ->whereBetween('end_date', [$dataInicio, $dataFim])
            ->get();
        $reservas_kits = KitReserve::all();
        $kits = Kit::all();
        $reservas_itens = ItemReserve::all();
        $itens = Item::all();

        $export = new class($reservas, $reservas_kits, $kits, $reservas_itens, $itens) implements FromView
        {
            protected $reservas;
            protected $reservas_kits;
            protected $kits;
            protected $reservas_itens;
            protected $itens;

            public function __construct($reservas, $reservas_kits, $kits, $reservas_itens, $itens)
            {
                $this->reservas = $reservas;
                $this->reservas_kits = $reservas_kits;
                $this->kits = $kits;
                $this->reservas_itens = $reservas_itens;
                $this->itens = $itens;
            }

            public function view(): View
            {
                return view('admin.reports.Excelres', [
                    'reservas' => $this->reservas,
                    'reservas_kits' => $this->reservas_kits,
                    'kits' => $this->kits,
                    'reservas_itens' => $this->reservas_itens,
                    'itens' => $this->itens
                ]);
            }
        };

        // Formatando as datas para o nome do arquivo no formato dmy
        $dataInicioFormatted = date('dmY', strtotime($dataInicio));
        $dataFimFormatted = date('dmY', strtotime($dataFim));
        $fileName = 'RelatorioReservas_' . $dataInicioFormatted . '_to_' . $dataFimFormatted . '.xlsx';

        return Excel::download($export, $fileName);
    }

    public function ExcelResLia(Request $request)
    {
        // Obter as datas de início e fim dos inputs
        $dataInicio = $request->input('dataIniciolia');
        $dataFim = $request->input('dataFimlia');

        // Verificar se as datas foram fornecidas
        if ($dataInicio == null || $dataFim == null) {
            return redirect()->back()->with('toast_error', 'Por favor, selecione as datas de início e fim!');
        }

        // Filtrar as reservas baseado nas datas
        $reservas = SpaceReserve::whereBetween('start_date', [$dataInicio, $dataFim])
            ->whereBetween('end_date', [$dataInicio, $dataFim])
            ->get();
        $users = User::all();

        $export = new class($reservas, $users) implements FromView
        {
            protected $reservas;
            protected $users;

            public function __construct($reservas, $users)
            {
                $this->reservas = $reservas;
                $this->users = $users;
            }

            public function view(): View
            {
                return view('admin.reports.Excelreslia', [
                    'reservas' => $this->reservas,
                    'users' => $this->users
                ]);
            }
        };

        // Formatando as datas para o nome do arquivo no formato dmy
        $dataInicioFormatted = date('dmY', strtotime($dataInicio));
        $dataFimFormatted = date('dmY', strtotime($dataFim));
        $fileName = 'RelatorioReservasLia_' . $dataInicioFormatted . '_to_' . $dataFimFormatted . '.xlsx';

        return Excel::download($export, $fileName);
    }
}