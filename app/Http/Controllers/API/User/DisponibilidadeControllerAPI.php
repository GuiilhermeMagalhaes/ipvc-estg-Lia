<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Disponibilidade;
use Carbon\Carbon;

class DisponibilidadeControllerAPI extends Controller
{
    public function index()
    {
        try {
            // Só datas de hoje em diante e não expiradas
            $horarios = Disponibilidade::whereDate('data', '>=', Carbon::today()->toDateString())
                ->where(function ($q) {
                    $q->whereNull('data_expiracao')
                      ->orWhere('data_expiracao', '>=', Carbon::now());
                })
                ->orderBy('data')
                ->get(['data', 'descricao']);

            $dados = $horarios->map(function ($h) {
                return [
                    'data'      => Carbon::parse($h->data)->format('Y-m-d'),
                    'descricao' => $h->descricao,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $dados,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar disponibilidade: ' . $e->getMessage()
            ], 500);
        }
    }
}