<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas públicas (Não precisam de Token)
Route::post('/register', 'API\Auth\AuthControllerAPI@register');
Route::post('/login', 'API\Auth\AuthControllerAPI@login');

// ROTAS PROTEGIDAS (Só entra quem enviar um Token válido)
Route::middleware('auth:sanctum')->group(function () {
    
    // Fazer Logout
    Route::post('/logout', 'API\Auth\AuthControllerAPI@logout');

    // Rota simples para testar se o Token está a funcionar e obter os dados do utilizador
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });

    Route::post('/reservas/criar', 'API\User\ReserveControllerAPI@store');

    // Rota para obter o catálogo de Kits e Itens (com contagem de unidades disponíveis)
    Route::get('/catalogo', 'API\User\ItemControllerAPI@index');

    Route::get('/item/{id}', 'API\User\ItemControllerAPI@show');

    // Rota para atualizar o perfil do utilizador
    Route::post('/perfil/edit', 'API\User\ProfileControllerAPI@update');

    Route::get('/reservas/historico', 'API\User\ReserveControllerAPI@index');


    Route::get('/centros-custo', 'API\User\CostCenterControllerAPI@index');

});
