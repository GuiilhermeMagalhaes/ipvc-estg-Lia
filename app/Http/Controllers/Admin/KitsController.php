<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kit;
use App\Models\ItemCategorie;
use App\Models\KitReserve;
use App\Models\KitUnity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ItemUnity;



class KitsController extends Controller
{


public function index(Request $request)
    {
        if ($request->ajax()) {
            $output = '';
            $search = $request->search;

            // Iniciamos a query pelas UNIDADES com o estado igual a 1
            $query = KitUnity::with('kit')->where('kit_unity_state_id', 1);

            if (!empty($search)) {
                // Pesquisa pelo nome do Kit associado à unidade
                $query->where(function($q) use ($search) {
                    $q->whereHas('kit', function($subQuery) use ($search) {
                        $subQuery->where('name', 'LIKE', '%' . $search . '%');
                    });
                });
            }

            $unidades = $query->get();

            if ($unidades->count() > 0) {
                foreach ($unidades as $unidade) {
                    $output .= '<div class="col-sm-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column justify-content-center text-center">
                                            <h5 class="card-title font-weight-bold">' . htmlspecialchars($unidade->kit->name, ENT_QUOTES, 'UTF-8') . '</h5>
                                            <p class="text-dark mb-1"><strong>LIA:</strong> ' . htmlspecialchars($unidade->lia_code, ENT_QUOTES, 'UTF-8') . '</p>
                                            <p class="card-text">' . number_format($unidade->kit->price_day, 2, ',', '.') . '€ / dia</p>
                                            
                                            {{-- Passamos o ID da UNIDADE no link de detalhes --}}
                                            <a class="btn btn-primary mx-auto" href="' . route('kits.show', ['id' => $unidade->id]) . '">VER DETALHES</a>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<div class="col-12"><p class="text-muted text-center">Nenhuma unidade de kit ativa encontrada.</p></div>';
            }

            return response()->json($output);
        } else {
            // Carregamento normal da página: traz apenas as unidades cujo estado é 1
            $unidades = KitUnity::with('kit')->where('kit_unity_state_id', 1)->get();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            // Enviamos a variável '$unidades' para a view
            return view('admin.kitUnity.index', ['unidades' => $unidades]);
        }
        return redirect('/');
    }



    // 1. APENAS VALIDA E GUARDA O KIT NA SESSÃO (NÃO GRAVA NA BD AINDA)
public function store(Request $request)
{
    $request->validate(
        [
            'name' => 'required|string|max:191',
            'description' => 'required|string|max:191',
            'price' => 'required|numeric|min:0',
            'price_day' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ],
        [
            'name.required' => 'O kit deve ter um nome',
            'description.required' => 'O kit deve ter uma descrição',
            'price.required' => 'O kit deve ter um preço associado', 
            'price_day.required' => 'O kit deve ter um preço por dia associado',
            'quantity.required' => 'Insira a quantidade total',
        ]
    );

    // Trata o ficheiro de imagem enviado pelo utilizador temporariamente
    if ($request->hasFile('image') && $request->file('image') != null) {
        $request->image->image_resize = true;
        $request->image->image_x = 400;
        $request->image->image_y = 300;
        
        $imagePath = $request->file('image');
        $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
        // Guarda na pasta final de imediato
        $path = $request->file('image')->storeAs('images/kits', $imageName, 'public');
    } else {
        $path = "images/empty.png";
    }

    // Une os dados textuais ao caminho da imagem
    $dadosParaSessao = $request->only(['name', 'description', 'ipvc_ref', 'price', 'price_day', 'quantity']);
    $dadosParaSessao['image_path'] = $path;

    // Guarda tudo dentro da sessão com a chave 'dados_do_kit'
    $request->session()->put('dados_do_kit', $dadosParaSessao);

    return redirect()->route('kits.createUnities');
}

// 2. EXIBE A VIEW DE UNIDADES BUSCANDO OS DADOS DA SESSÃO
public function createUnities(Request $request)
{
    // Se o utilizador tentar aceder a esta página sem passar pelo passo 1, volta para trás
    if (!$request->session()->has('dados_do_kit')) {
        return redirect()->route('kits.create')->with('toast_error', 'Por favor, preencha primeiro os dados do kit.');
    }

    $dadosKit = $request->session()->get('dados_do_kit');
    
    // Captura as unidades de itens que ainda não pertencem a nenhum kit
    $itensLivres = ItemUnity::with('item')->whereNull('kit_unity_id')->get();

    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        return view('admin.kitUnity.create', [
            'kitName' => $dadosKit['name'],
            'quantity' => $dadosKit['quantity'],
            'itensLivres' => $itensLivres
        ]);
    }
    return redirect('/');
}

// 3. GRAVA TUDO EM SIMULTÂNEO (SÓ AQUI ENTRA NA BASE DE DADOS)
public function storeUnities(Request $request)
{
    // 1. Verifica se os dados do Kit ainda estão na sessão
    if (!$request->session()->has('dados_do_kit')) {
        return redirect()->route('kits.create')->with('toast_error', 'Sessão expirada. Recomece o processo.');
    }

    $dadosKit = $request->session()->get('dados_do_kit');
    $quantity = $dadosKit['quantity'];

    $regras = [
        'lia_codes' => 'required|array',
    ];

    $mensagens = [
        'lia_codes.*.required' => 'O código LIA é obrigatório.',
        'lia_codes.*.unique'   => 'Este código LIA já existe no sistema.',
        'lia_codes.*.distinct' => 'Inseriu códigos LIA duplicados.',
    ];


    // Força a validação individual de cada índice do loop
    for ($i = 0; $i < $quantity; $i++) {
        $regras["lia_codes.$i"] = 'required|string|distinct|unique:kit_unity,lia_code';
        $regras["items_for_unity.$i"] = 'required|array|min:1';
        
        $mensagens["items_for_unity.$i.required"] = 'É obrigatório associar pelo menos 1 item a esta unidade.';
        $mensagens["items_for_unity.$i.min"] = 'É obrigatório associar pelo menos 1 item a esta unidade.';
    }

    // 2. Executa a validação com as regras construídas
    $request->validate($regras, $mensagens);

    // 2. Valida os códigos LIA que vieram do formulário
    /*$request->validate([
        'lia_codes' => 'required|array',
        'lia_codes.*' => 'required|string|distinct|unique:kit_unity,lia_code',
        'items_for_unity' => 'required|array',
        'items_for_unity.*' => 'required|array|min:1',
    ], [
        'lia_codes.*.required' => 'O código LIA é obrigatório.',
        'lia_codes.*.unique'   => 'Este código LIA já existe no sistema.',
        'lia_codes.*.distinct' => 'Inseriu códigos LIA duplicados.',
         'items_for_unity.*.required' => 'É obrigatório associar pelo menos 1 item a esta unidade.',
        'items_for_unity.*.min'      => 'É obrigatório associar pelo menos 1 item a esta unidade.',
       
    ]);
*/
    $dadosKit = $request->session()->get('dados_do_kit');

    // 3. Inicia a transação: Se algo falhar no meio, nada é gravado
    DB::transaction(function () use ($request, $dadosKit) {
        
        // Criar o Kit Pai na BD
        $kit = new Kit();
        $kit->name = $dadosKit['name'];
        $kit->description = $dadosKit['description'];
        $kit->ipvc_ref = $dadosKit['ipvc_ref'];
        $kit->price = $dadosKit['price'];
        $kit->price_day = $dadosKit['price_day'];
        $kit->quantity = $dadosKit['quantity'];
        $kit->image = $dadosKit['image_path'];
        $kit->save();

        // Criar as Unidades físicas do Kit conectadas ao ID gerado agora
        foreach ($request->lia_codes as $index => $liaCode) {
            $kitUnity = new KitUnity();
            $kitUnity->lia_code = $liaCode;
            $kitUnity->kit_id = $kit->id;
            $kitUnity->kit_unity_state_id = 1; 
            $kitUnity->save();

            // Associar as unidades de itens selecionadas a esta unidade do kit
            if (isset($request->items_for_unity[$index]) && is_array($request->items_for_unity[$index])) {
                foreach ($request->items_for_unity[$index] as $itemId) {
                    $itemUnity = ItemUnity::find($itemId);
                    if ($itemUnity) {
                        $itemUnity->kit_unity_id = $kitUnity->id;
                        $itemUnity->save();
                    }
                }
            }
        }
    });

    // 4. Limpa a sessão para não deixar lixo em memória
    $request->session()->forget('dados_do_kit');

    Alert::success('Sucesso', 'Kit e respetivas unidades gravados com sucesso!');
    return redirect()->route('kits.index');
}

    public function create()
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            return view('admin.kits.create', [
                'categorias' => ItemCategorie::all()
            ]);
        }
        return redirect('/');
    }
/*
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'description' => 'required',
                'price' => 'required',
                'price_day'     => 'required|numeric',
                'quantity'      => 'required|integer|min:0',
                'quantity_disp' => 'required|integer|min:0'
            ],
            [
                'name.required' => 'O kit deve ter um nome',
                'description.required' => 'O kit deve ter uma descrição',
                'price.required' => 'O kit deve ter um preço associado', 
                'price_day.required'     => 'O kit deve ter um preço por dia associado',
                'quantity.required'      => 'Insira a quantidade total',
                'quantity_disp.required' => 'Insira a quantidade disponível para requisição',
            ]
        );

        if ($request->image != null) {
            $request->image->image_resize = true;
            $request->image->image_x = 400;
            $request->image->image_y = 300;
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/kits', $imageName, 'public');
        } else {
            $path = "images/empty.png";
        }

        Kit::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'ipvc_ref' => $request->ipvc_ref,
            'price_day'     => $request->price_day,
            'quantity'      => $request->quantity,
            'quantity_disp' => $request->quantity_disp,
            'image' => $path,
            //'categoria_id' => $request->categoria_id,

        ]);

        return redirect('admin/kits')->with('toast_success', 'Kit criado com sucesso!');
    }
    */

    public function show($id)
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            return view('admin.kits.show', [
                'kit' => Kit::find($id),
                'categoria' => ItemCategorie::all()
            ]);
        }
        return redirect('/');
    }

    public function edit($id)
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            return view('admin.kits.edit', [
                'kit' => Kit::find($id),
                'categorias' => ItemCategorie::all()
            ]);
        }
        return redirect('/');
    }

    public function update(Request $request, $id)
    {
        $kit = Kit::find($id);

        if ($request->image != null) {
            $request->image->image_resize = true;
            $request->image->image_x = 400;
            $request->image->image_y = 300;
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/kits', $imageName, 'public');
        } else {
            $path = $kit->image;
        }

        $request->validate(
            [
                'name' => 'required',
                'description' => 'required',
                'price' => 'required'
            ],
            [
                'name.required' => 'O kit deve ter um nome',
                'description.required' => 'O kit deve ter uma descrição',
                'price.required' => 'O kit deve ter um preço associado'
            ]
        );

        $kit->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'ipvc_ref' => $request->ipvc_ref,
            'image' => $path,
            'categoria_id' => $request->categoria_id
        ]);

        return redirect(route('kits.show', $kit->id));
    }

    public function destroy($id)
    {
        $kitsReserva = KitReserve::where('kit_id', $id)->get();
        if ($kitsReserva->isEmpty()) {
            $kit = Kit::find($id);
            $kit->delete();
        } else {
            return redirect()->to('/admin/kits/')->with('toast_error', 'Existe uma reserva com este kit!');
        }
        return redirect('/admin/kits');
    }


    public function ocultos(Request $request)
{
    // Construímos a query base filtrando apenas pelo estado id = 2 (ocultas)
    // Usamos o with('kit') para evitar o problema do N+1 nas relações
    $query = KitUnity::with('kit')->where('kit_unity_state_id', 2)->whereHas('kit');

    // Se houver uma pesquisa em curso
    if ($request->has('search') && !empty($request->input('search'))) {
        $search = $request->input('search');

        $query->where(function($q) use ($search) {
            // Pesquisa pelo Código LIA da Unidade
            $q->where('lia_code', 'LIKE', '%' . $search . '%')
              // OU pesquisa pelo Nome do Kit Pai na tabela relacionada
              ->orWhereHas('kit', function($kitQuery) use ($search) {
                  $kitQuery->where('name', 'LIKE', '%' . $search . '%');
              });
        });
    }

    

    $unidades = $query->get();

    // Se for uma requisição AJAX, retorna apenas os cartões renderizados
    if ($request->ajax()) {
        $html = '';
        foreach ($unidades as $unidade) {
            $html .= '
            <div class="col-sm-3 mb-4">
                <div class="card h-100 border-secondary">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <h5 class="card-title font-weight-bold">' . e($unidade->kit->name) . '</h5>
                        <p class="text-dark mb-1"><strong>LIA:</strong> ' . e($unidade->lia_code) . '</p>
                        <p class="card-text">' . number_format($unidade->kit->price_day, 2, ',', '.') . ' € / dia</p>
                        <a class="btn btn-secondary mx-auto" style="width: 140px;" href="' . route('kitUnity.show', ['id' => $unidade->id]) . '">VER DETALHES</a>
                    </div>
                </div>
            </div>';
        }
        return response()->json($html);
    }

    // Se for o carregamento normal da página, renderiza a view completa
    return view('admin.kitunity.ocultos', compact('unidades'));
}
    
}



/*
class KitsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Captura o valor da pesquisa
            $search = $request->search;

            if (!empty($search)) {
            // Consulta os itens disponíveis conforme a pesquisa
            $kits = Kit::where('kit_state_id', '=', 1)
                ->where('name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('lia_code', 'LIKE', '%' . $request->search . '%')
                ->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();
            } else {
                $kits = Kit::where('kit_state_id', '=', 1)->get();
            }

            // Constrói o HTML para cada item encontrado
            if ($kits->count() > 0) {
                foreach ($kits as $kit) {
                    $output .= '<div class="col-sm-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column justify-content-center text-center">
                                            <h5 class="card-title">' . htmlspecialchars($kit->name, ENT_QUOTES, 'UTF-8') . '</h5>
                                            <p class="card-text">' . htmlspecialchars($kit->lia_code, ENT_QUOTES, 'UTF-8') . '</p>
                                            <p class="card-text">' . number_format($kit->price, 2, ',', '.') . '€ / dia</p>
                                            <a class="btn btn-primary mx-auto" href="' . route('kits.show', ['id' => $kit->id]) . '">VER DETALHES</a>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }else {
            $kits = Kit::where('kit_state_id', '=', 1)->get();
        }
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            return view('admin.kits.index', ['kits' => $kits]);
        }
        return redirect('/');
    }

    public function indexocultos(Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Captura o valor da pesquisa
            $search = $request->search;

            if (!empty($search)) {
                // Consulta os itens disponíveis conforme a pesquisa
                $kits = Kit::where('kit_state_id', '!=', 1)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('lia_code', 'LIKE', '%' . $search . '%')
                        ->orWhereRaw("LOWER(name) LIKE ?", ['%' . strtolower($search) . '%']);
                })
                    ->get();
            } else {
                // Se a pesquisa estiver vazia, não retorna nenhum resultado
                $kits = Kit::where('kit_state_id', '!=', 1)->get();
            }

            // Constrói o HTML para cada item encontrado
            if ($kits->count() > 0) {
                foreach ($kits as $kit) {
                    $output .= '<div class="col-sm-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column justify-content-center text-center">
                                        <h5 class="card-title">' . htmlspecialchars($kit->name, ENT_QUOTES, 'UTF-8') . '</h5>
                                        <p class="card-text">' . htmlspecialchars($kit->lia_code, ENT_QUOTES, 'UTF-8') . '</p>
                                        <p class="card-text">' . number_format($kit->price, 2, ',', '.') . '€ / dia</p>
                                        <a class="btn btn-primary mx-auto" href="' . route('kits.show', ['id' => $kit->id]) . '">VER DETALHES</a>
                                    </div>
                                </div>
                            </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        } else {
            $kits = Kit::where('kit_state_id', '!=', 1)->get();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.kits.indexocultos', ['kits' => $kits]);
        }
        return redirect('/');
    }

    public function create()
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            return view('admin.kits.create', [
                'categorias' => ItemCategorie::all(),
                //'itens' => Item::where('item_state_id', '=', 1)->where('kit_id', null)->get()
            ]);
        }
        return redirect('/');
        
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'description' => 'required',
                //'lia_code' => 'required|unique:kits,lia_code',
                'price' => 'required'
            ],
            [
                'name.required' => 'O kit deve ter um nome',
                'description.required' => 'O kit deve ter uma descrição',
                //'lia_code.required' => 'O kit deve ter um código LIA associado',
                //'lia_code.unique' => 'Código LIA deve ser único',
                'price.required' => 'O kit deve ter um preço associado'
            ]
        );

        if ($request->image != null) {
            $request->image->image_resize = true;
            $request->image->image_x = 400;
            $request->image->image_y = 300;
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/kits', $imageName, 'public');
        } else {
            $path = "images/empty.png";
        }

        $parentKit = Kit::create([
            'name' => $request->name,
            'description' => $request->description,
            //'lia_code' => $request->lia_code,
            'price' => $request->price,
            'ipvc_ref' => $request->ipvc_ref,
            'kit_state_id' => $request->state,
            'image' => $path,
            'categoria_id' => $request->categoria_id,
        ]);

        if ($request->itens != null) {
            foreach ($request->itens as $id) {
                $Item = Item::find($id);
                $Item->kit_id = $parentKit->id;
                $Item->save();
            }
        }

        return redirect('admin/kits')->with('toast_success', 'Kit criado com sucesso!');
    }

    public function show($id)
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            return view('admin.kits.show', [
                'kit' => Kit::find($id),
                'categoria' => ItemCategorie::all()
            ]);
        }
        return redirect('/');
        
    }

    public function edit($id)
    {
        if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2){
            return view('admin.kits.edit', [
                'kit' => Kit::find($id),
                'categorias' => ItemCategorie::all(),
                'itensKit' => Item::where('kit_id', $id)->get(),
                'itens' => Item::where('item_state_id', '=', 1)->where('kit_id', null)->get()
            ]);
        }
        return redirect('/');
    }

    public function update(Request $request, $id)
    {
        $kit = Kit::find($id);

        if ($request->image != null) {
            $request->image->image_resize = true;
            $request->image->image_x = 400;
            $request->image->image_y = 300;
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/kits', $imageName, 'public');
        } else {
            $path = $kit->image;
        }

        $request->validate(
            [
                'name' => 'required',
                'description' => 'required',
                'lia_code' => ['required', Rule::unique('kits', 'lia_code')->ignore($id)],
                'price' => 'required'
            ],
            [
                'name.required' => 'O kit deve ter um nome',
                'description.required' => 'O kit deve ter uma descrição',
                'lia_code.required' => 'O kit deve ter um código LIA associado',
                'lia_code.unique' => 'Código LIA deve ser único',
                'price.required' => 'O kit deve ter um preço associado'
            ]
        );


        $kit->update([
            'name' => $request->name,
            'description' => $request->description,
            'lia_code' => $request->lia_code,
            'price' => $request->price,
            'ipvc_ref' => $request->ipvc_ref,
            'kit_state_id' => $request->state,
            'image' => $path,
            'categoria_id' => $request->categoria_id
        ]);

        $kit->save();

        $itensKit = Item::where('kit_id', $id)->get();

        foreach ($itensKit as $item) {
            $item->kit_id = null;
            $item->save();
        }

        if ($request->itens != null) {
            foreach ($request->itens as $id) {
                $Item = Item::find($id);
                $Item->kit_id = $kit->id;
                $Item->save();
            }
        }
        
        return redirect(route('kits.show', $kit->id));
    }

    public function destroy($id)
    {
        $kitsReserva = KitReserve::where('kit_id', $id)->get();
        if ($kitsReserva->isEmpty()) {
            $itensKit = Item::where('kit_id', $id)->get();

            foreach ($itensKit as $item) {
                $item->kit_id = null;
                $item->save();
            }

            $kit = Kit::find($id);
            $kit->delete();
        } else {
            return redirect()->to('/admin/kits/')->with('toast_error', 'Existe uma reserva com este kit!');
        }
        return redirect('/admin/kits');
        
    }
}
*/


    /*public function indexocultos(Request $request)
    {
        if ($request->ajax()) {
            $output = '';
            $search = $request->search;

            if (!empty($search)) {
                $kits = Kit::where('kit_state_id', '!=', 1)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhereRaw("LOWER(name) LIKE ?", ['%' . strtolower($search) . '%']);
                })
                ->get();
            } else {
                $kits = Kit::where('kit_state_id', '!=', 1)->get();
            }

            if ($kits->count() > 0) {
                foreach ($kits as $kit) {
                    $output .= '<div class="col-sm-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column justify-content-center text-center">
                                        <h5 class="card-title">' . htmlspecialchars($kit->name, ENT_QUOTES, 'UTF-8') . '</h5>
                                        <p class="card-text">' . number_format($kit->price, 2, ',', '.') . '€ / dia</p>
                                        <a class="btn btn-primary mx-auto" href="' . route('kits.show', ['id' => $kit->id]) . '">VER DETALHES</a>
                                    </div>
                                </div>
                            </div>';
                }
            } else {
                $output = '<p>Nenhum kit encontrado.</p>';
            }

            return response()->json($output);
        } else {
            $kits = Kit::where('kit_state_id', '!=', 1)->get();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.kits.indexocultos', ['kits' => $kits]);
        }
        return redirect('/');
    }
*/