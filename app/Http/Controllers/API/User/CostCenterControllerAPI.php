<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\CostCenter; // Usa o nome exato do teu Model

class CostCenterControllerAPI extends Controller
{
    public function index()
    {
        try {
            // Vai buscar todos os centros de custo ativos
            $centros = CostCenter::select('id', 'name')->get();

            return response()->json([
                'status' => 'success',
                'data' => $centros
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar centros de custo.'
            ], 500);
        }
    }
}