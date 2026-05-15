<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Disponibilidade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DisponibilidadeController extends Controller
{
    public function index()
    {
        $dias=[];
        $descricoes=[];
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $horarios = Disponibilidade::whereMonth('data', '=', $month)->whereYear('data', '=', $year)->get();

        foreach ($horarios as $horario) {
            $data = date("d", strtotime($horario->data));
            $dias[] = intval($data);
            $descricoes[] = $horario->descricao;
        }

        return view('user.disponibilidade.info', ['dias' => $dias, 'descricoes'=>$descricoes, 'month'=>$month, 'year'=>$year]);
    }

    public function nextMonth($oldMonth, $oldYear)
    {
        $dias=[];
        $descricoes=[];
        $month = $oldMonth+1;
        if($month == 13){
            $month = 1;
            $year=$oldYear+1;
        }else{
            $year=$oldYear;
        }

        $horarios = Disponibilidade::whereMonth('data', '=', $month)->whereYear('data', '=', $year)->get();

        foreach ($horarios as $horario) {
            $data = date("d", strtotime($horario->data));
            $dias[] = intval($data);
            $descricoes[] = $horario->descricao;
        }

        return view('user.disponibilidade.info', ['dias' => $dias, 'descricoes'=>$descricoes, 'month'=>$month, 'year'=>$year]);
    }
    
    public function previousMonth($oldMonth, $oldYear)
    {
        $dias=[];
        $descricoes=[];
        $month = $oldMonth-1;
        if($month == 0){
            $month = 12;
            $year=$oldYear-1;
        }else{
            $year=$oldYear;
        }

        $horarios = Disponibilidade::whereMonth('data', '=', $month)->whereYear('data', '=', $year)->get();

        foreach ($horarios as $horario) {
            $data = date("d", strtotime($horario->data));
            $dias[] = intval($data);
            $descricoes[] = $horario->descricao;
        }

        return view('user.disponibilidade.info', ['dias' => $dias, 'descricoes'=>$descricoes, 'month'=>$month, 'year'=>$year]);
    }
}
