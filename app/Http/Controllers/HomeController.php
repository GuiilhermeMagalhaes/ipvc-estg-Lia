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

            // Obter todos os centros de custo
            $centros = CostCenter::all();

            // Calcular total_cost de todos os centros de custo
            $totalCost = $centros->sum('total_cost');

            // Calcular total_debt de todos os centros de custo
            $totalDebt = $centros->sum('total_debt');

            $users = User::all();
            // Calcula o número total de usuários
            $totalUsers = $users->count();

            $reserves = Reserve::all();

            $ongoingReserves = $reserves->filter(function ($reserve) {
                return $reserve->reserveState->id == 7 || $reserve->reserveState->id == 8;
            });
            // Contar reservas em andamento (estados 7 e 8)
            $ongoingCount = $reserves->filter(function ($reserve) {
                return $reserve->reserveState->id == 7 || $reserve->reserveState->id == 8;
            })->count();
            // Contar reservas pendentes (estado 1)
            $pendingCount = $reserves->filter(function ($reserve) {
                return $reserve->reserveState->id == 1;
            })->count();
            // Filtrar reservas em atraso (estado 7 e todaydate > end_date)
            $delayedReserves = $reserves->filter(function ($reserve) {
                return $reserve->reserveState->id == 4;
            });

           
            $stats = Reserve::select('cost_center_id', DB::raw('count(*) as total'))
                ->groupBy('cost_center_id')
                ->with('costCenter')
                ->orderByDesc('total') // Ordena do maior para o mais pequeno
                ->limit(10) // Corta a lista nos 10 primeiros
                ->get();

            $labels = $stats->map(function($stat) {
                return $stat->costCenter->name ?? 'Sem Centro'; 
            })->toArray();

            $values = $stats->pluck('total')->toArray();
            

            return view('layouts.admin-home', [
                'reserves' => $reserves,
                'ongoingReserves' => $ongoingReserves,
                'ongoingCount' => $ongoingCount,
                'pendingCount' => $pendingCount,
                'delayedReserves' => $delayedReserves,
                'totalCost' => $totalCost,
                'totalDebt' => $totalDebt,
                'totalUsers' => $totalUsers,
                'labels' => $labels, // Variável enviada para o JS do gráfico
                'values' => $values  // Variável enviada para o JS do gráfico
            ]);
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