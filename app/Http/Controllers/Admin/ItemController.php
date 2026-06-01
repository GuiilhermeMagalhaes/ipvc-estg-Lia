<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Kit;
use App\Models\ItemCategorie;
use App\Models\ItemReserve;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\ItemUnity;

class ItemController extends Controller
{
    
    public function index(Request $request)
{
    if ($request->ajax()) {
        $output = '';
        $search = $request->search;

   // Procure a linha onde inicia o query:
$query = ItemUnity::with('item')->where('item_unity_state_id', 1);

if (!empty($search)) {
    $query->whereHas('item', function($q) use ($search) {
        $q->where('nome', 'LIKE', '%' . $search . '%')
          ->orWhere('ipvc_ref', 'LIKE', '%' . $search . '%')
          ->orWhere('model', 'LIKE', '%' . $search . '%');
    });
}

$unidades = $query->get();

if ($unidades->count() > 0) {
    foreach ($unidades as $unidade) {
        $output .= '<div class="col-sm-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-center text-center">
                                <h1 class="card-title">' . htmlspecialchars($unidade->item->nome, ENT_QUOTES, 'UTF-8') . '</h1>
                                <small class="text-muted mb-2">LIA: ' . htmlspecialchars($unidade->lia_code, ENT_QUOTES, 'UTF-8') . '</small>
                                <p class="card-text">' . htmlspecialchars($unidade->item->ipvc_ref, ENT_QUOTES, 'UTF-8') . '</p>
                                <p class="card-text card-text-preco">' . number_format($unidade->item->preco, 2, ',', '.') . ' € / dia</p>
                                <a class="btn btn-primary mx-auto" style="width: 140px;" href="' . route('itens.show', ['id' => $unidade->id]) . '">VER DETALHES</a>
                            </div>
                        </div>
                    </div>';
    }
} else {
    $output = '<p>Nenhum item encontrado.</p>';
}

return response()->json($output);
}

else {
    // Busca as unidades ativas trazendo o item agarrado
    $unidades = ItemUnity::with('item')->where('item_unity_state_id', 1)->get();
}

if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
    // Aponta para a nova pasta 'itemUnities' e envia a variável '$unidades'
    return view('admin.itemUnities.index', ['unidades' => $unidades]);
}
return redirect('/');
}

    

    public function show($id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        // Buscamos a UNIDADE específica pelo ID dela, trazendo o item e o estado associados
        $unidade = ItemUnity::with(['item', 'itemUnityState'])->find($id);

        if (!$unidade) {
            return redirect()->route('itens.index')->with('toast_error', 'Unidade não encontrada.');
        }

        $unidadesDoItem = ItemUnity::where('item_id', $unidade->item_id)
                                       ->whereIn('item_unity_state_id', [1, 2])
                                       ->get();

        return view('admin.itemUnities.show', [
            'unidade'   => $unidade,
            'item'      => $unidade->item, // Enviamos o item pai para não quebrar a tua estrutura
            'categoria' => ItemCategorie::all(),
            'unidadesDoItem' => $unidadesDoItem
        ]);
    }
    return redirect('/');
}

    public function create()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itens.create', [
                'categorias' => ItemCategorie::all(),
                //'itens' => Item::where('item_state_id', '=', 1)->where('kit_id', null)->get()
            ]);
        }
        return redirect('/');
    }



    public function store(Request $request)
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            $request->validate(
                [
                    'nome' => 'required',
                    'model' => 'required',
                    'preco' => 'required|numeric|min:0',
                    'price_day'     => 'required|numeric|min:0',
                    'quantity'      => 'required|integer|min:1',
                    'quantity_disp' => 'required|integer|min:0|lte:quantity',
                    'categoria_id'  => 'required|exists:item_categories,id',
                
                ],
                [
                    'nome.required' => 'O item deve ter um nome',
                    'model.required' => 'O item deve ter um modelo',
                    'preco.required' => 'O item deve ter um preço associado',
                    'price_day.required'     => 'O item deve ter um preço por dia associado.',
                    'quantity.required'      => 'Insira a quantidade total.',
                    'quantity_disp.required' => 'Insira a quantidade disponível para requisição.',

                    'quantity_disp.integer'  => 'A quantidade disponível deve ser um número inteiro.',
                    'quantity.integer'       => 'A quantidade total deve ser um número inteiro.',
                    
                    'preco.min'              => 'O preço não pode ser inferior a 0.',
                    'price_day.min'          => 'O preço por dia não pode ser inferior a 0.',
                    'quantity.min'           => 'A quantidade total deve ser pelo menos 1.',
                    'quantity_disp.min'      => 'A quantidade disponível não pode ser inferior a 0.',

                    'quantity_disp.lte'      => 'A quantidade disponível não pode ser superior à quantidade total.',

                    
                ]
            );

            if ($request->image != null) {
                $imagePath = $request->file('image');
                $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
                $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
            } else {
                $path = "images/empty.png";
            }

                // MUDANÇA AQUI: Juntamos todos os dados do formulário num array
            $itemData = $request->only(['ipvc_ref', 'serial_number', 'nome', 'model', 'observation', 'acessorio', 'preco', 'categoria_id', 'price_day', 'quantity', 'quantity_disp']);
            $itemData['image'] = $path; // adiciona o caminho da imagem

            // Guardamos tudo na sessão. Nada foi para a BD ainda!
            return redirect()->route('itens.createUnities')->with([
                'item_data' => $itemData,
                'item_nome' => $request->nome,
                'quantity'  => $request->quantity
            ]);
        }
        return redirect('/');
    }



        public function createUnities()
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            $itemData  = session('item_data');
            $item_nome = session('item_nome');
            $quantity  = session('quantity');

            if (!$itemData || !$quantity) {
                return redirect()->route('itens.create')->with('toast_error', 'Por favor, preencha os dados do item primeiro.');
            }

            // Segura os dados do item na sessão para o próximo clique de botão
            session()->flash('item_data', $itemData);
            session()->flash('item_nome', $item_nome);
            session()->flash('quantity', $quantity);

            return view('admin.itemUnities.create', compact('item_nome', 'quantity'));
        }
        return redirect('/');
    }

    public function storeUnities(Request $request)
{
    if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
        // 1. Recupera os dados do item que estavam guardados na sessão
        $itemData = session('item_data');

        if (!$itemData) {
            return redirect()->route('itens.create')->with('toast_error', 'Sessão expirada. Volte a preencher os dados do item.');
        }

        // 2. Valida os códigos LIA enviados do formulário
        $request->validate([
            'lia_codes'  => 'required|array',
            'lia_codes.*'=> 'required|string|distinct|unique:item_unity,lia_code',
        ], [
            'lia_codes.*.required' => 'O código LIA é obrigatório.',
            'lia_codes.*.unique'   => 'Este código LIA já existe no sistema.',
            'lia_codes.*.distinct' => 'Inseriu códigos LIA duplicados.',
        ]);

        // 3. ABRE A TRANSAÇÃO: Ou cria tudo com sucesso, ou não cria absolutamente nada
        \Illuminate\Support\Facades\DB::transaction(function () use ($itemData, $request) {
            
            // Cria o Item Principal na BD neste momento
            $item = Item::create($itemData);

            // Cria todas as unidades associadas ao ID do item acabado de gerar
            foreach ($request->lia_codes as $code) {
                ItemUnity::create([
                    'lia_code'            => $code,
                    'item_id'             => $item->id,
                    'kit_unity_id'        => null,
                    'item_unity_state_id' => 1 
                ]);
            }
        });

        return redirect('admin/itens')->with('toast_success', 'Item criado com sucesso!');
    }
    return redirect('/');
}



public function ocultos(Request $request)
{
    // 1. SE FOR A PESQUISA (AJAX)
    if ($request->ajax()) {
        $output = '';
        $search = $request->search;

        // FILTRO MUDA AQUI: procuramos apenas pelo estado 2 (Oculto)
        $query = ItemUnity::with('item')->where('item_unity_state_id', 2);

        if (!empty($search)) {
            $query->whereHas('item', function($q) use ($search) {
                $q->where('nome', 'LIKE', '%' . $search . '%')
                  ->orWhere('ipvc_ref', 'LIKE', '%' . $search . '%')
                  ->orWhere('model', 'LIKE', '%' . $search . '%');
            });
        }

        $unidades = $query->get();

        if ($unidades->count() > 0) {
            foreach ($unidades as $unidade) {
                $output .= '<div class="col-sm-3 mb-4">
                                <div class="card h-100 bg-light"> <div class="card-body d-flex flex-column justify-content-center text-center">
                                        <h1 class="card-title text-muted">' . htmlspecialchars($unidade->item->nome, ENT_QUOTES, 'UTF-8') . '</h1>
                                        <small class="text-danger mb-2">LIA: ' . htmlspecialchars($unidade->lia_code, ENT_QUOTES, 'UTF-8') . ' (Oculto)</small>
                                        <p class="card-text">' . htmlspecialchars($unidade->item->ipvc_ref, ENT_QUOTES, 'UTF-8') . '</p>
                                        <p class="card-text card-text-preco">' . number_format($unidade->item->preco, 2, ',', '.') . ' € / dia</p>
                                        <a class="btn btn-secondary mx-auto" style="width: 140px;" href="' . route('itens.show', ['id' => $unidade->item->id]) . '">VER DETALHES</a>
                                    </div>
                                </div>
                            </div>';
            }
        } else {
            $output = '<p>Nenhum item oculto encontrado.</p>';
        }

        return response()->json($output);
    } 
    
    // 2. SE FOR O CARREGAMENTO NORMAL
    else {
        // FILTRO MUDA AQUI: estado 2
        $unidades = ItemUnity::with('item')->where('item_unity_state_id', 2)->get();
    }

    // 3. SEGURANÇA E REDIRECIONAMENTO
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        return view('admin.itemUnities.ocultos', ['unidades' => $unidades]);
    }
    
    return redirect('/');
}


public function updateUnity(Request $request, $id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        
        // Valida se o LIA foi preenchido e se é único (ignorando o ID desta própria unidade)
        $request->validate([
            'lia_code' => 'required|string|unique:item_unity,lia_code,' . $id,
            'item_unity_state_id' => 'required|exists:item_unity_states,id'
        ], [
            'lia_code.required' => 'O código LIA não pode ficar vazio.',
            'lia_code.unique' => 'Este código LIA já está a ser usado noutra unidade.',
        ]);

        $unidade = ItemUnity::find($id);
        
        if ($unidade) {
            // Atualiza apenas os dois campos da unidade física
            $unidade->update($request->only(['lia_code', 'item_unity_state_id']));
            return redirect()->route('itens.show', $id)->with('toast_success', 'Unidade atualizada com sucesso!');
        }

        return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
    }
    return redirect('/');
}


public function edit($id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        
        $item = Item::find($id);
        $categorias = ItemCategorie::all();

        if (!$item) {
            return redirect()->route('itens.index')->with('toast_error', 'Item não encontrado.');
        }

        return view('admin.itens.edit', compact('item', 'categorias'));
    }
    return redirect('/');
}

public function update(Request $request, $id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        
        $item = Item::find($id);
        if (!$item) {
            return redirect()->route('itens.index')->with('toast_error', 'Item não encontrado.');
        }

        // 1. Validar dados gerais e impedir que a quantidade seja menor que a atual
        $request->validate([
            'nome' => 'required',
            'model' => 'required',
            'preco' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:item_categories,id',
            'quantity' => 'required|integer|min:' . $item->quantity, // Bloqueia diminuição
            'quantity_disp' => 'required|integer|min:0',
        ], [
            'quantity.min' => 'Não é permitido diminuir a quantidade total de itens já registados (' . $item->quantity . ').',
        ]);

        // 2. Tratar o upload da imagem (se o utilizador carregou uma nova)
        $path = $item->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
        }

        // 3. GUARDAR NA SESSÃO (Não faz update na BD ainda!)
        $dadosItem = $request->except(['image']);
        $dadosItem['image'] = $path; // adiciona o caminho da foto certa
        session(['dados_item_edicao' => $dadosItem]);

        // 4. Calcular quantas novas unidades vão ser criadas com base no aumento
        $unidadesAtuais = ItemUnity::where('item_id', $item->id)->get();
        $novasUnidadesQtd = $request->quantity - $unidadesAtuais->count();

        // 5. Avança para a página seguinte enviando os dados de suporte
        return view('admin.itemUnities.edit', [
            'item' => $item,
            'unidadesAtuais' => $unidadesAtuais,
            'novasUnidadesQtd' => $novasUnidadesQtd
        ]);
    }
    return redirect('/');
}


public function updateUnitiesEtapa(Request $request, $id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        $item = Item::find($id);

        // 1. Validar os códigos LIA vindos da segunda página
        $request->validate([
            'lias_atuais.*' => 'required|string',
            'novos_lias.*' => 'sometimes|required|string'
        ], [
            'lias_atuais.*.required' => 'O código LIA existente não pode ficar vazio.',
            'novos_lias.*.required' => 'Deves preencher o código LIA para as novas unidades.',
        ]);

        // 2. Recuperar os dados gerais do Item que guardámos na Sessão no Passo 1
        $dadosItem = session('dados_item_edicao');

        if (!$dadosItem) {
            return redirect()->route('itens.edit', $id)->with('toast_error', 'Sessão expirada. Por favor tente novamente.');
        }

        // 3. AGORA SIM: Fazemos o update real na tabela de Itens
        $item->update([
            'nome' => $dadosItem['nome'],
            'model' => $dadosItem['model'],
            'ipvc_ref' => $dadosItem['ipvc_ref'] ?? null,
            'serial_number' => $dadosItem['serial_number'] ?? null,
            'preco' => $dadosItem['preco'],
            'price_day' => $dadosItem['price_day'] ?? null,
            'quantity' => $dadosItem['quantity'],
            'quantity_disp' => $dadosItem['quantity_disp'],
            'observation' => $dadosItem['observation'] ?? null,
            'acessorio' => $dadosItem['acessorio'] ?? null,
            'image' => $dadosItem['image'],
            'categoria_id' => $dadosItem['categoria_id'], 
            'data_aquisicao' => $request->data_aquisicao
        ]);

        // 4. Atualizar os LIAs das unidades antigas que foram modificados
        if ($request->has('lias_atuais')) {
            foreach ($request->lias_atuais as $unityId => $liaCode) {
                $unity = ItemUnity::find($unityId);
                if ($unity) {
                    $unity->update(['lia_code' => $liaCode]);
                }
            }
        }

        // 5. Criar as novas unidades físicas com os novos LIAs preenchidos
        if ($request->has('novos_lias')) {
            foreach ($request->novos_lias as $novoLia) {
                ItemUnity::create([
                    'item_id' => $item->id,
                    'lia_code' => $novoLia,
                    'item_unity_state_id' => 1 // Ativo por defeito
                ]);
            }
        }

        // Limpa a sessão para não deixar lixo em memória
        session()->forget('dados_item_edicao');

        return redirect()->route('itens.index')->with('toast_success', 'Item e unidades gravados com sucesso!');
    }
    return redirect('/');
}



    public function anularUnity($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            
            $unidade = ItemUnity::find($id);

            if (!$unidade) {
                return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
            }

            // Usamos uma Transação para garantir que faz as duas operações ou nenhuma
            \Illuminate\Support\Facades\DB::transaction(function () use ($unidade) {
                // 1. Atualiza o estado da unidade para 3 (Anulado)
                $unidade->update([
                    'item_unity_state_id' => 3 // Substitui pelo ID correto do teu estado "Anulado"
                ]);

                // 2. Procura o Item pai e retira 1 unidade ao stock total e disponível
                $item = $unidade->item;
                if ($item) {
                    $item->decrement('quantity', 1);
                    $item->decrement('quantity_disp', 1);
                }
            });

            return redirect()->route('itens.index')->with('toast_success', 'Unidade anulada e stock atualizado com sucesso!');
        }
        return redirect('/');
    }
}

