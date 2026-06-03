<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\KitsController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LiaSpaceController as AdminLiaSpaceController;
use App\Http\Controllers\Admin\ReserveController as AdminReserveController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CentroCustoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\User\KitsController as UserKitsController;
use App\Http\Controllers\User\ItemController as UserItemController;
use App\Http\Controllers\User\LiaSpaceController;
use App\Http\Controllers\User\ReserveController;
use App\Http\Controllers\User\PerfilController;
use App\Http\Controllers\User\DisponibilidadeController as UserDisponibilidadeController;
use App\Http\Controllers\Admin\DisponibilidadeController as AdminDisponibilidadeController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/**
 * @description Mostra o ecra inicial
 */
Route::get('/', [HomeController::class, 'index']);

Auth::routes();

// Rotas de verificação de email
Route::get('/email/verify', [VerificationController::class, 'show'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/home', [HomeController::class, 'adminIndex'])->name('admin.home');
        Route::get('/downloaddisp', [HomeController::class, 'PDFItensDisp'])->name('pdfitensdisp-download');
        Route::get('/downloadind', [HomeController::class, 'PDFItensInd'])->name('pdfitensind-download');
        Route::post('/downloadres', [HomeController::class, 'ExcelRes'])->name('excelres-download');
        Route::post('/downloadreslia', [HomeController::class, 'ExcelResLia'])->name('excelreslia-download');
        Route::prefix('kits')->group(function () {
            Route::get('/', [KitsController::class, 'index'])->name('kits.index');
            //Route::get('/indexocultos', [KitsController::class, 'indexocultos'])->name('kits.indexocultos');
            Route::get('/ocultos', [KitsController::class, 'ocultos'])->name('kits.indexocultos');
            Route::get('/create', [KitsController::class, 'create'])->name('kits.create');
            Route::post('/', [KitsController::class, 'store'])->name('kits.store');
             Route::get('/create-unities', [KitsController::class, 'createUnities'])->name('kits.createUnities');
            Route::post('/store-unities', [KitsController::class, 'storeUnities'])->name('kits.storeUnities');
            Route::get('/{id}', [KitsController::class, 'show'])->name('kits.show');
            Route::get('/{id}/edit', [KitsController::class, 'edit'])->name('kits.edit');
            Route::put('/{id}', [KitsController::class, 'update'])->name('kits.update');
            Route::delete('{id}', [KitsController::class, 'destroy'])->name('kits.destroy');
            Route::get('/edit/searchitens', [ItemController::class, 'searchItens'])->name('search.itens');
            
           
        });

        Route::prefix('itens')->group(function () {
            Route::get('/', [ItemController::class, 'index'])->name('itens.index');
            Route::get('/ocultos', [ItemController::class, 'ocultos'])->name('itens.ocultos');
            Route::get('/create', [ItemController::class, 'create'])->name('itens.create');
            Route::post('/', [ItemController::class, 'store'])->name('itens.store');
            Route::get('/create-unities', [ItemController::class, 'createUnities'])->name('itens.createUnities');
            Route::post('/store-unities', [ItemController::class, 'storeUnities'])->name('itens.storeUnities');
            Route::get('/{id}', [ItemController::class, 'show'])->name('itens.show');
            Route::get('/{id}/edit', [ItemController::class, 'edit'])->name('itens.edit');
            Route::put('/{id}', [ItemController::class, 'update'])->name('itens.update');
            Route::put('/{id}/update-unity', [ItemController::class, 'updateUnity'])->name('unidades.updateUnity');
            // Rota para processar a segunda etapa da edição (LIAs e novas unidades)
            Route::post('/{id}/update-unities-step', [ItemController::class, 'updateUnitiesEtapa'])->name('itens.updateUnitiesEtapa');
            Route::delete('/{id}/anular', [ItemController::class, 'anularUnity'])->name('unidades.anular');
            Route::delete('{id}', [ItemController::class, 'destroy'])->name('itens.destroy');
            // Adiciona esta linha junto das outras rotas de Kits/Unidades:
            Route::get('/kit-unities/{id}', [KitUnityController::class, 'show'])->name('kitUnity.show');
        });

        Route::prefix('/reserves')->group(function () {
            Route::get('/', [AdminReserveController::class, 'all'])->name('reserves.all');
            Route::get('/pending', [AdminReserveController::class, 'pending'])->name('reserves.pending');
            Route::get('/delayed', [AdminReserveController::class, 'delayed'])->name('reserves.delayed');
            Route::get('/ongoing', [AdminReserveController::class, 'ongoing'])->name('reserves.ongoing');
            Route::get('/unauthorized', [AdminReserveController::class, 'unauthorized'])->name('reserves.unauthorized');
            Route::get('/completed', [AdminReserveController::class, 'completed'])->name('reserves.completed');
            Route::get('/{id}', [AdminReserveController::class, 'show'])->name('reserves.show');
            Route::get('/download/{id}', [AdminReserveController::class, 'PDFDownload'])->name('pdf-download');
            Route::post('/{id}/autorize', [AdminReserveController::class, 'autorize'])->name('reserve.autorize');
            Route::post('/{id}/decline', [AdminReserveController::class, 'decline'])->name('reserve.decline');
            Route::post('/{id}/finalize', [AdminReserveController::class, 'finalize'])->name('reserve.finalize');
            Route::post('/{id}/deliver', [AdminReserveController::class, 'deliver'])->name('reserve.deliver');
            Route::post('/{id}/receive', [AdminReserveController::class, 'receive'])->name('reserve.receive');
        });

        Route::prefix('/categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/create', [CategoryController::class, 'create'])->name('category.create');
            Route::post('/store', [CategoryController::class, 'store'])->name('category.store');
            Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
            Route::post('/{id}', [CategoryController::class, 'update'])->name('category.update');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');

        });

        Route::prefix('/centros')->group(function () {
            Route::get('/', [CentroCustoController::class, 'index'])->name('centro.index');
            Route::get('/reservas/{id}', [CentroCustoController::class, 'reservas'])->name('centro.reservas');
            Route::get('/pagar/{id}', [CentroCustoController::class, 'pagar'])->name('centro.pagar');
            Route::get('/create', [CentroCustoController::class, 'create'])->name('centro.create');
            Route::post('/store', [CentroCustoController::class, 'store'])->name('centro.store');
            Route::get('/{id}', [CentroCustoController::class, 'destroy'])->name('centro.destroy');
        });

        Route::prefix('/lia-space')->group(function () {
            Route::get('/', [AdminLiaSpaceController::class, 'index'])->name('space.index');
            Route::post('/', [AdminLiaSpaceController::class, 'getSpace']);
            Route::get('/create/{id}', [AdminLiaSpaceController::class, 'create']);
            Route::post('/{id}', [AdminLiaSpaceController::class, 'store'])->name('space.store');
            Route::get('/{id}/edit', [AdminLiaSpaceController::class, 'edit']);
            Route::put('/{id}', [AdminLiaSpaceController::class, 'update'])->name('lia_space.update');
            Route::delete('{id}', [AdminLiaSpaceController::class, 'delete']);
            Route::get('/reservas', [LiaSpaceController::class, 'reservas'])->name('space.reservas');
            Route::get('/bolseiro/{postoID}', [AdminLiaSpaceController::class, 'getBolseiro']);
            Route::get('/showreserve/{postoID}', [AdminLiaSpaceController::class, 'showReservationForm']);
            Route::get('/itens/search', [AdminLiaSpaceController::class, 'searchItens'])->name('itens.search');
            Route::get('/{id}/reserve', [AdminLiaSpaceController::class, 'reserve']);
            Route::post('/reserve/{id}', [AdminLiaSpaceController::class, 'createReserve'])->name('lia.space.reserve');
            Route::get('/{id}/editbolseiro', [AdminLiaSpaceController::class, 'editBolseiro']);
            Route::put('/load/{id}', [AdminLiaSpaceController::class, 'load'])->name('lia_space.load');
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('user.index');
            Route::get('/{id}', [UserController::class, 'show'])->name('user.show');
            Route::put('/{id}', [UserController::class, 'update'])->name('user.update');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        });

        Route::prefix('disponibilidade')->group(function () {
            Route::get('/', [AdminDisponibilidadeController::class, 'index'])->name('disponibilidade.info');
            Route::get('/create', [AdminDisponibilidadeController::class, 'create'])->name('disponibilidade.create');
            Route::get('/{id}', [AdminDisponibilidadeController::class, 'destroy'])->name('disponibilidade.destroy');
            Route::get('/{id}/edit', [AdminDisponibilidadeController::class, 'edit'])->name('disponibilidade.edit');
            Route::put('/{id}', [AdminDisponibilidadeController::class, 'update'])->name('disponibilidade.update');
            Route::delete('disponibilidade', [AdminDisponibilidadeController::class, 'destroyAll'])->name('disponibilidade.destroyAll');
            Route::post('/store', [AdminDisponibilidadeController::class, 'store'])->name('disponibilidade.store');
        });
    });

    Route::prefix('reserve')->group(function () {
        Route::get('/', [ReserveController::class, 'index'])->name('reserve.index');
        Route::post('/create', [ReserveController::class, 'create'])->name('reserve.create');
        Route::get('/info', [ReserveController::class, 'reserveInfo']);
        Route::post('/add-kit/{id}', [ReserveController::class, 'addKit'])->name('kit.add');
        Route::post('/add-item/{id}', [ReserveController::class, 'additem'])->name('item.add');
        Route::post('/remove-kit/{id}', [ReserveController::class, 'removeKit'])->name('kit.remove');
        Route::post('/remove-item/{id}', [ReserveController::class, 'removeItem'])->name('item.remove');
        Route::post('/reserve-cancel', [ReserveController::class, 'cancelReserve'])->name('reserve.cancel');
        Route::post('/reserve-confirm', [ReserveController::class, 'confirmReserve'])->name('reserve.confirm');
    });

    Route::prefix('lia-space')->group(function () {
        Route::get('/', [LiaSpaceController::class, 'index']);
        Route::post('/', [LiaSpaceController::class, 'getSpace']);
        Route::post('/availability', [LiaSpaceController::class, 'checkAvailability']);
        Route::get('/reserve', [LiaSpaceController::class, 'reserve']);
        Route::post('/reserve/{id}', [LiaSpaceController::class, 'createReserve'])->name('space.reserve');
        Route::get('/callendar', [LiaSpaceController::class, 'callendar']);
        Route::get('/postos', [LiaSpaceController::class, 'getPostos']);
        Route::get('/reservas', [LiaSpaceController::class, 'getReservas']);
    });

    Route::prefix('perfil')->group(function () {
        Route::get('/', [PerfilController::class, 'index'])->name('perfil.index');
        Route::get('/edit', [PerfilController::class, 'edit'])->name('perfil.edit');
        Route::get('/reserves', [PerfilController::class, 'reserves'])->name('perfil.reserves');
        Route::put('/update', [PerfilController::class, 'update'])->name('perfil.update');
    });

    Route::get('disponibilidade', [UserDisponibilidadeController::class, 'index'])->name('disponibilidade.index');
    Route::get('disponibilidade/proximo/{oldMonth}/{oldYear}', [UserDisponibilidadeController::class, 'nextMonth'])->name('disponibilidade.next');
    Route::get('disponibilidade/anterior/{oldMonth}/{oldYear}', [UserDisponibilidadeController::class, 'previousMonth'])->name('disponibilidade.previous');
});

Route::get('/orientador/create', [CentroCustoController::class, 'orientadorCreate'])->name('orientador.centro.create');

Route::get('/kits', [UserKitsController::class, 'index'])->name('user.kits.index');
Route::get('/kits/disponivel', [UserKitsController::class, 'disponivel'])->name('user.kits.disponivel');
Route::get('/kits/indisponivel', [UserKitsController::class, 'indisponivel'])->name('user.kits.indisponivel');
Route::get('/kits/all', [UserKitsController::class, 'all'])->name('user.kits.all');
Route::get('/kit/{id}', [UserKitsController::class, 'show']);


Route::get('/categoria/{id}', [UserItemController::class, 'index'])->name('user.categoria.index');
Route::get('/categoria/disponivel/{id}', [UserItemController::class, 'disponivel'])->name('user.categoria.disponivel');
Route::get('/categoria/indisponivel/{id}', [UserItemController::class, 'indisponivel'])->name('user.categoria.indisponivel');
Route::get('/categoria/all/{id}', [UserItemController::class, 'all'])->name('user.categoria.all');
Route::get('/item/{id}', [UserItemController::class, 'show']);

Route::get('/download-pdf', function () {
    $path = storage_path('pdf/Manual de Utilização-Reservar Equipamento.pdf');
    return response()->download($path, 'Manual de Utilização-Reservar Equipamento.pdf');
})->name('download.pdf');
