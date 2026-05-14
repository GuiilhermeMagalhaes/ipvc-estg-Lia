<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiaSpace;
use App\Models\SpaceItem;
use App\Models\SpaceReserve;
use App\Models\CostCenter;
use App\Models\User;
use App\Models\Item;
use App\Models\Kit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class LiaSpaceController extends Controller
{
    public function index()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.lia_space.index');
        }
        return redirect('/');
    }

    public function getSpace(Request $request)
    {
        $space = LiaSpace::where('space_code', $request->spaceID)->first();
        if ($space == null) {
            return response()->json(['space' => $space]);
        }
        return response()->json(['space' => $space, 'itens' => $space->itens]);
    }

    public function reserve($id) {
        // Verificar permissões do utilizador
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }
    
        // Procurar o espaço pelo código
        $space = LiaSpace::where('space_code', $id)->first();
    
        if ($space == null) {
            return response()->json(['error' => 'Espaço não encontrado.'], 404);
        }
    
        // Criar um "Request" falso para passar para o método getBolseiro
        $mockRequest = new \Illuminate\Http\Request();
        $mockRequest->merge(['spaceID' => $space->space_code]);
    
        // Obter o bolseiro usando o método getBolseiro
        $bolseiroResponse = $this->getBolseiro($mockRequest);
        $bolseiroData = json_decode($bolseiroResponse->getContent(), true); // Decodificar a resposta JSON
    
        // Verificar se há reservas ativas
        if (!empty($bolseiroData['bolseiro'])) {
            return view('admin.lia_space.reserve', [
                'space' => $space,
                'costCenters' => CostCenter::all(),
                'users' => User::where('user_type_id', 5)->get(),
                'currentReservation' => $bolseiroData['bolseiro'][0] // Passar a primeira reserva ativa
            ]);
        }
    
        // Caso não haja reservas, carregar o formulário vazio
        return view('admin.lia_space.reserve', [
            'space' => $space,
            'costCenters' => CostCenter::all(),
            'users' => User::where('user_type_id', 5)->get(),
            'start_date' => null,
            'end_date' => null,
            'description' => null
        ]);
    }
    
    public function createReserve(Request $request, $id){

        $request->validate([
            'description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cost_center_id' => 'required|exists:cost_centers,id',
            'occupant_id' => 'nullable|exists:users,id',
            'occupant_email' => 'nullable|email',
        ]);

        // Determinar o utilizador da reserva
        $user = $request->occupant_email
            ? $this->newUser($request) // Criar novo utilizador
            : User::find($request->occupant_id);

        if (!$user) {
            return redirect('/admin/lia-space')->with('success', 'O utilizador selecionado não foi encontrado.');
        }
        
        $space = LiaSpace::where('id', $id)->first();

        if (!$space) {
            return redirect('/admin/lia-space')->with('success', 'O posto selecionado não foi encontrado.');
        }

        $reserve = SpaceReserve::create([
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'cost' => $space->cost,
            'occupant_id' => $user->id,
            'user_id' => Auth::id(),
            'space_code' => $space->space_code,
        ]);

        $reserve->liaSpace()->attach($space->id);
        $reserve->users()->attach(Auth::id());

        $costCenter = CostCenter::find($request->cost_center_id);
        $costCenter->total_cost = $costCenter->total_cost + $space->cost;
        $costCenter->total_debt = $costCenter->total_debt + $space->cost;
        $costCenter->save();

        return redirect('/admin/lia-space')->with('success', 'Reserva realizada!');
    }

    public function editBolseiro($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            $space = SpaceReserve::where('space_code', $id)->first();
            if ($space == null) {
                return response()->json(['space' => $space]);
            }

            // Buscar todos os users
            $users = User::where('user_type_id', 5)->get();

              // Buscar a reserva ativa para o espaço com base na data atual
            $currentDate = now(); // Obtém a data atual
            $space = SpaceReserve::where('space_code', $id)
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->first(); // Encontra a reserva ativa
            
            return view('admin.lia_space.load', [
                    'space' => $space,
                    'costCenters' => CostCenter::all(),
                    'users' => $users,
            ]);

        }
        return redirect('/');
    }

    public function load(Request $request, $id)
    {
        $space = SpaceReserve::where('space_code', $id)->first();
        $user = User::find($request->occupant_id);

        $request->validate(
            [
                'description' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'occupant_id' => 'nullable|exists:users,id',
            ],
            [
                'description.required' => 'A reserva deve ter uma descrição',
                'start_date.required' => 'Insira a data de início',
                'end_date.end_date.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início',
                'email.required' => 'Escolha um email',
            ]
        );

        $space->update([
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'occupant_id' => $user->id,
            'user_id' => Auth::id(),
        ]);

        $space->save();

        return redirect('/admin/lia-space')->with('toast_success', 'Reserva Atualizada!');
    }

    public function searchItens(Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Captura o valor da pesquisa
            $search = $request->search;

            if (!empty($search)) {
                $itens = Item::where('nome', 'LIKE', '%' . $search . '%')
                            ->orWhere('lia_code', 'LIKE', '%' . $search . '%')
                            ->get();

                $kits = Kit::where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('lia_code', 'LIKE', '%' . $search . '%')
                            ->get();

                $combinedResults = $itens->merge($kits);
            } else {
                // Se não houver pesquisa, retornar todos os itens
                $itens = Item::all();
                $kits = Kit::all();

                $combinedResults = $itens->merge($kits);
            }

            $count = 0;
            // Constrói o HTML para cada item encontrado
            if ($combinedResults->count() > 0) {
                foreach ($combinedResults as $item) {
                    // if ($count >= 5) {
                    //     break; // Interrompe o loop após 5 iterações
                    // }
                    if ($item instanceof Item) {
                        // Verifica se o item tem um nome
                        $liaCodeHtml = $item->lia_code ? 
                            '<span style="color:gray; font-size:0.8em; display:block;">' . htmlspecialchars($item->lia_code, ENT_QUOTES, 'UTF-8') . '</span>' 
                            : '';
    
                        // Adiciona o tipo "Item" à sugestão
                        $output .= '<div class="dropdown-item" data-id="' . $item->id . '" data-lia_code="' . $item->lia_code . '">' 
                                . '<strong>Item: </strong>' . htmlspecialchars($item->nome, ENT_QUOTES, 'UTF-8') 
                                . ' | ' . $liaCodeHtml 
                                . '</div>';
                    }
    
                    if ($item instanceof Kit) {
                        // Verifica se o kit tem um nome
                        $liaCodeHtml = $item->lia_code ? 
                            '<span style="color:gray; font-size:0.8em; display:block;">' . htmlspecialchars($item->lia_code, ENT_QUOTES, 'UTF-8') . '</span>' 
                            : '';
    
                        // Adiciona o tipo "Kit" à sugestão
                        $output .= '<div class="dropdown-item" data-id="' . $item->id . '" data-lia_code="' . $item->lia_code . '">' 
                                . '<strong>Kit: </strong>' . htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8') 
                                . ' | ' . $liaCodeHtml 
                                . '</div>';
                    }

                    $count++;
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }
    }

    // public function searchItens(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $output = '';

    //         // Captura o valor da pesquisa
    //         $search = $request->search;

    //         if (!empty($search)) {
    //             $itens = Item::where('nome', 'LIKE', '%' . $search . '%')
    //                         ->get();

    //             $kits = Kit::where('name', 'LIKE', '%' . $search . '%')
    //                         ->get();

    //             $combinedResults = $itens->merge($kits);
    //         } else {
    //             // Se não houver pesquisa, retornar todos os itens
    //             $itens = Item::all();
    //             $kits = Kit::all();

    //             $combinedResults = $itens->merge($kits);
    //         }

    //         $count = 0;
    //         // Constrói o HTML para cada item encontrado
    //         if ($combinedResults->count() > 0) {
    //             foreach ($combinedResults as $item) {
    //                 if ($count >= 5) {
    //                     break; // Interrompe o loop após 5 iterações
    //                 }
    //                 if($item->nome != null){
    //                     $output .= '<div class="dropdown-item" data-id="' . $item->id . '">' 
    //                             . htmlspecialchars($item->nome, ENT_QUOTES, 'UTF-8') . '</div>';
    //                 }

    //                 $count++;
    //             }
    //         } else {
    //             $output = '<p>Nenhum item encontrado.</p>';
    //         }

    //         return response()->json($output);
    //     }
    // }

    public function getBolseiro(Request $request){
        $today = \Carbon\Carbon::now();
        $space = LiaSpace::where('space_code', $request->spaceID)->first();

        if (!$space) {
            return response()->json(['error' => 'Espaço não encontrado.'], 404);
        }

        $reservasComBolseiros = DB::table('space_reserves as sr')
            ->join('lia_spaces as ls', 'ls.space_code', '=', 'sr.space_code') 
            ->join('users as u', 'u.id', '=', 'sr.occupant_id')  // Junção com a tabela users
            ->select(
                'sr.description as description',
                'sr.start_date as data_inicio',
                'sr.end_date as data_fim',
                'u.id as id',
                'u.name as bolseiro',
                'u.email as email'
            )
            ->where('sr.space_code', '=', $request->postoID)  // Filtro para o código do espaço
            ->where('sr.start_date', '<=', $today)  // Apenas reservas ativas ou já iniciadas
            ->where('sr.end_date', '>=', $today)  // Apenas reservas ainda válidas
            ->get();
    

        return response()->json(['bolseiro' => $reservasComBolseiros]);
    }

    public function edit($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            $space = LiaSpace::where('space_code', $id)->first();
            if ($space == null) {
                return response()->json(['space' => $space]);
            }

            // Buscar todos os users
            $users = User::all();

            $itens = SpaceItem::where('lia_space_id', $space->id)->get();
            
            return view('admin.lia_space.edit', [
                'space' => $space,
                'itens' => $itens,
                'users' => $users
            ]);
        }
        return redirect('/');
    }

    public function create($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.lia_space.create', ['id' => $id]);
        }
        return redirect('/');
    }

    public function store(Request $request, $id)
    {
        $request->validate(
            [
                'description' => 'required',
                'pc' => 'required',
                'teclado' => 'required',
                'rato' => 'required',
                'lia_code' => 'required',
                'price' => 'required',
                'itens.*' => 'required',
                'lia_codes.*' => 'required',
            ],
            [
                'description.required' => 'O espaço deve ter uma descrição',
                'pc.required' => 'Insira o computador do espaço',
                'teclado.required' => 'Insira o teclado do espaço',
                'rato.required' => 'Insira o rato do espaço',
                'lia_code.required' => 'Insira um código do Lia para este espaço',
                'price.required' => 'O espaço deve ter um preço associado',
                'itens.*.required' => 'Tentou adicionar itens sem descrição',
                'lia_codes.*.required' => 'Tentou adicionar itens sem referência',
            ]
        );

        $space = LiaSpace::create([
            'description' => $request->description,
            'pc' => $request->pc,
            'teclado' => $request->teclado,
            'rato' => $request->rato,
            'lia_code' => $request->lia_code,
            'cost' => $request->price,
            'space_code' =>  $id
        ]);

        $space->save();

    //   // Adiciona os itens ao espaço, se existirem
    //     if (!empty($request->itens)) { 
    //         foreach ($request->itens as $key => $item) { 
    //             // Aqui estamos aceder os itens e os códigos LIA de maneira correta
    //             SpaceItem::create([
    //                 'lia_space_id' => $space->id,  // ID do espaço
    //                 'lia_code' => $request->lia_codes[$key],  // Aceder o código LIA correspondente ao item
    //                 'description' => $item  // Descrição do item
    //             ]);
    //         }
    //     }

        // Verificar se os itens estão disponíveis
        if (!empty($request->itens)) { 
            foreach ($request->itens as $key => $item) {

                // Verificar se o item já está associado a um posto de trabalho
                $itemInAnotherPost = SpaceItem::where('lia_code', $request->lia_codes[$key])
                                            ->exists();

                if ($itemInAnotherPost) {
                    return redirect('/admin/lia-space/create/'.$id)->with('toast_error', 'O item já está associado a outro posto de trabalho.');
                }

                // Crie a ligação entre o item e o posto de trabalho
                SpaceItem::create([
                    'lia_space_id' => $space->id,  // ID do espaço
                    'lia_code' => $request->lia_codes[$key],  // Aceder o código LIA correspondente ao item
                    'description' => $item  // Descrição do item
                ]);
            }
        }

        return redirect('/admin/lia-space')->with('toast_success', 'Novo espaço criado');
    }

    public function update(Request $request, $id)
    {
        $space = LiaSpace::where('space_code', $id)->first();

        $request->validate(
            [
                'description' => 'required',
                'pc' => 'required',
                'teclado' => 'required',
                'rato' => 'required',
                'lia_code' => 'required',
                'cost' => 'required',
                'itens.*' => 'required',
                'kits.*' => 'required',
                'lia_codes.*' => 'required',
            ],
            [
                'description.required' => 'O espaço deve ter uma descrição',
                'pc.required' => 'Insira o computador do espaço',
                'teclado.required' => 'Insira o teclado do espaço',
                'rato.required' => 'Insira o rato do espaço',
                'lia_code.required' => 'Insira um código do Lia para este espaço',
                'cost.required' => 'O espaço deve ter um preço associado',
                'itens.*.required' => 'Tentou adicionar itens sem descrição',
                'kits.*.required' => 'Tentou adicionar kits sem descrição',
                'lia_codes.*.required' => 'Tentou adicionar itens sem referência',
            ]
        );

        $space->update([
            'description' => $request->description,
            'pc' => $request->pc,
            'teclado' => $request->teclado,
            'rato' => $request->rato,
            'lia_code' => $request->lia_code,
            'cost' => $request->cost,
            'space_code' =>  $id
        ]);

        $space->save();
        
        $itensAntigos = $space-> itens;
        foreach ($itensAntigos as $item){
            $item->delete();
        }

        // if($request->itens > 0){
        //     foreach ($request->itens as $item) {
        //         SpaceItem::create([
        //             'lia_space_id' => $space->id,
        //             'description' => $item
        //         ]);
        //     }
        // }

        // Verificar se os itens estão disponíveis
        if (!empty($request->itens)) { 
            foreach ($request->itens as $key => $item) {

                // Verificar se o item já está associado a um posto de trabalho
                $itemInAnotherPost = SpaceItem::where('lia_code', $request->lia_codes[$key])
                                            ->exists();

                if ($itemInAnotherPost) {
                    return redirect('/admin/lia-space/'.$id.'/edit')->with('toast_error', 'O item já está associado a outro posto de trabalho.');
                }

                // Crie a ligação entre o item e o posto de trabalho
                SpaceItem::create([
                    'lia_space_id' => $space->id,  // ID do espaço
                    'lia_code' => $request->lia_codes[$key],  // Aceder o código LIA correspondente ao item
                    'description' => $item  // Descrição do item
                ]);
            }
        }

        $kitsAntigos = $space->kits ?? [];
        foreach ($kitsAntigos as $kit) {
            $kit->delete();
        }

        // if ($request->kits) {
        //     foreach ($request->kits as $kit) {
        //         SpaceItem::create([
        //             'lia_space_id' => $space->id,
        //             'description' => $kit,
        //         ]);
        //     }
        // }

        // Verificar se os kits estão disponíveis
        if (!empty($request->kits)) { 
            foreach ($request->kits as $key => $kit) {

                // Verificar se o kit já está associado a um posto de trabalho
                $itemInAnotherPost = SpaceItem::where('lia_code', $request->lia_codes[$key])
                                            ->exists();

                if ($itemInAnotherPost) {
                    return redirect('/admin/lia-space/'.$id.'/edit')->with('toast_error', 'O kit já está associado a outro posto de trabalho.');
                }

                // Crie a ligação entre o item e o posto de trabalho
                SpaceItem::create([
                    'lia_space_id' => $space->id,  // ID do espaço
                    'lia_code' => $request->lia_codes[$key],  // Aceder o código LIA correspondente ao kit
                    'description' => $kit  // Descrição do kit
                ]);
            }
        }

        return redirect('/admin/lia-space')->with('toast_success', 'Espaço Atualizado!');
    }

    public function delete($id)
    {
        $today = \Carbon\Carbon::now();

        $space = LiaSpace::where('space_code', $id)->first();
        
        $reservations = SpaceReserve::where('space_code', $id)->get();
        foreach ($reservations as $reserve) {
            // Verificar se a data de término da reserva ainda não passou
            if (\Carbon\Carbon::parse($reserve->end_date)->isFuture()) {
                $reserve->end_date = $today; // Definir o 'end_date' como a data e hora atual
                $reserve->save(); // Salvar a atualização
            }
        }
        
        $space->space_code = null;
        $space->save();

        return;
    }
}
