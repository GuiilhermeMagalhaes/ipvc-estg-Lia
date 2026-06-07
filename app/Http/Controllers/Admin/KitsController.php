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

           
            $query = KitUnity::with('kit')->where('kit_unity_state_id', 1);

            

          if (!empty($search)) {
            $query->where(function($q) use ($search) {
                
                $q->where('lia_code', 'LIKE', '%' . $search . '%')
                  
                  ->orWhereHas('kit', function($subQuery) use ($search) {
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
                                        <h5 class="card-title">' . htmlspecialchars($unidade->kit->name, ENT_QUOTES, 'UTF-8') . '</h5>
                                        <small class="text-muted mb-2">Ref: ' . htmlspecialchars($unidade->kit->ipvc_ref ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</small>
                                        <p class="text-muted mb-2">LIA: ' . htmlspecialchars($unidade->lia_code, ENT_QUOTES, 'UTF-8') . '</p>
                                        <p class="card-text card-text-preco">' . number_format($unidade->kit->price_day, 2, ',', '.') . '€ / dia</p>
                                        
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
            
            $unidades = KitUnity::with('kit')->where('kit_unity_state_id', 1)->get();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            
            return view('admin.kitUnities.index', ['unidades' => $unidades]);
        }
        return redirect('/');
    }


      public function ocultos(Request $request)
{

    $query = KitUnity::with('kit')->where('kit_unity_state_id', 2)->whereHas('kit');

   if ($request->has('search') && !empty($request->input('search'))) {
        $search = $request->input('search');

        $query->where(function($q) use ($search) {
            $q->where('lia_code', 'LIKE', '%' . $search . '%')
              ->orWhereHas('kit', function($kitQuery) use ($search) {
                  $kitQuery->where('name', 'LIKE', '%' . $search . '%')
                           ->orWhere('model', 'LIKE', '%' . $search . '%');
              });
        });
    }

    $unidades = $query->get();

    if ($request->ajax()) {
        $html = '';
        foreach ($unidades as $unidade) {
            $html .= '<div class="col-sm-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column justify-content-center text-center">
                                        <h5 class="card-title">' . htmlspecialchars($unidade->kit->name, ENT_QUOTES, 'UTF-8') . '</h5>
                                        <small class="text-muted mb-2">Ref: ' . htmlspecialchars($unidade->kit->ipvc_ref ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</small>
                                        <p class="text-muted mb-2">LIA: ' . htmlspecialchars($unidade->lia_code, ENT_QUOTES, 'UTF-8') . '</p>
                                        <p class="card-text card-text-preco">' . number_format($unidade->kit->price_day, 2, ',', '.') . '€ / dia</p>
                                        
                                        <a class="btn btn-primary mx-auto" href="' . route('kits.show', ['id' => $unidade->id]) . '">VER DETALHES</a>
                                    </div>
                                </div>
                            </div>';
        }
        return response()->json($html);
    }

    return view('admin.kitunities.ocultos', compact('unidades'));
}



public function show($id)
    {
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        
        $unidade = KitUnity::with(['kit', 'itemUnities.item'])->find($id);

        if (!$unidade) {
            return redirect()->route('kits.index')->with('toast_error', 'Unidade de Kit não encontrada.');
        }

        $itensLivres = ItemUnity::with('item')->whereNull('kit_unity_id')->get();

        return view('admin.kitUnities.show', [
            'unidade'     => $unidade,
            'kit'         => $unidade->kit,
            'itensLivres' => $itensLivres
        ]);
    }


    public function updateUnity(Request $request, $id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        
        $request->validate([
            'lia_code' => 'required|string|unique:kit_unity,lia_code,' . $id,
            'kit_unity_state_id' => 'required|exists:kit_unity_states,id',
        ], [
            'lia_code.required' => 'O código LIA não pode ficar vazio.',
            'lia_code.unique' => 'Este código LIA já está a ser usado noutra unidade de kit.',
            'kit_unity_state_id.required' => 'O estado da unidade é obrigatório.',
            'kit_unity_state_id.exists' => 'O estado selecionado é inválido.'
        ]);

        
        $unidade = KitUnity::find($id);
        
        if ($unidade) {
            
           
            $unidade->update($request->only(['lia_code', 'kit_unity_state_id']));
            
            return redirect()->back()->with('toast_success', 'Unidade atualizada com sucesso!');
        }

        return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
    }
    
    return redirect('/');
}


    public function destroy($id)
    {
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $unidade = KitUnity::find($id);

        if (!$unidade) {
            return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
        }

        if ($unidade->kit_unity_state_id == 3) {
            return redirect()->back()->with('toast_error', 'Esta unidade já se encontra anulada.');
        }
    
        
        $unidade->kit_unity_state_id = 3;
        $unidade->save();

        if ($unidade->kit) {
       
            $unidade->kit->decrement('quantity', 1);
        }
    
        
        ItemUnity::where('kit_unity_id', $unidade->id)->update(['kit_unity_id' => null]);

        return redirect()->route('kits.index')->with('toast_success', 'Unidade anulada e stock atualizado com sucesso!!');
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




   
public function store(Request $request)
{
    if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
        return redirect('/');
    }
    $request->validate(
        [
            'name' => 'required|string|max:191',
            'description' => 'required|string|max:191',
            'price' => 'required|numeric|min:0',
            'price_day' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'ipvc_ref'    => 'nullable|string|max:191',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ],
        [
            'name.required'        => 'O kit deve ter um nome.',
            'description.required' => 'O kit deve ter uma descrição.',
            'price.required'       => 'O kit deve ter um preço associado.', 
            'price.min'            => 'O preço não pode ser inferior a 0.',
            'price_day.required'   => 'O kit deve ter um preço por dia associado.',
            'price_day.min'        => 'O preço por dia não pode ser inferior a 0.',
            'quantity.required'    => 'Insira a quantidade total.',
            'quantity.integer'     => 'A quantidade total deve ser um número inteiro.',
            'quantity.min'         => 'A quantidade total deve ser pelo menos 1.',
            'image.image'          => 'O ficheiro selecionado deve ser uma imagem.',
            'image.mimes'          => 'A imagem deve ser do formato: jpeg, png, jpg ou webp.',
            'image.max'            => 'A imagem não pode ter mais de 2MB.',
        ]
    );

    
    if ($request->image != null) {
        $imagePath = $request->file('image');
        $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
        $path = $request->file('image')->storeAs('images/kits', $imageName, 'public');
    } else {
        $path = "images/empty.png";
    }

    
    $dadosParaSessao = $request->only(['name', 'description', 'ipvc_ref', 'price', 'price_day', 'quantity']);
    $dadosParaSessao['image_path'] = $path;

   
    $request->session()->put('dados_do_kit', $dadosParaSessao);

    return redirect()->route('kits.createUnities')->with([
        'kit_nome' => $request->name,
        'quantity' => $request->quantity
    ]);
}








public function createUnities(Request $request)
{
    if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
        return redirect('/');
    }
    
    if (!$request->session()->has('dados_do_kit')) {
        return redirect()->route('kits.create')->with('toast_error', 'Por favor, preencha primeiro os dados do kit.');
    }

    $dadosKit = $request->session()->get('dados_do_kit');
    
    

    $itensLivres = ItemUnity::with('item')
        ->whereNull('kit_unity_id')
        ->whereIn('item_unity_state_id', [1, 2]) 
        ->get();

    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        return view('admin.kitUnities.create', [
            'kitName' => $dadosKit['name'],
            'quantity' => $dadosKit['quantity'],
            'itensLivres' => $itensLivres
        ]);
    }
    return redirect('/');
}




public function storeUnities(Request $request)
{
    if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
        return redirect('/');
    }
    
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

    
    for ($i = 0; $i < $quantity; $i++) {
        $regras["lia_codes.$i"] = 'required|string|distinct|unique:kit_unity,lia_code';
        $regras["items_for_unity.$i"] = 'required|array|min:1';
        
        $mensagens["items_for_unity.$i.required"] = 'É obrigatório associar pelo menos 1 item a esta unidade.';
        $mensagens["items_for_unity.$i.min"] = 'É obrigatório associar pelo menos 1 item a esta unidade.';
    }

    
    $request->validate($regras, $mensagens);

    $dadosKit = $request->session()->get('dados_do_kit');

    
    DB::transaction(function () use ($request, $dadosKit) {
        
       
        $kit = new Kit();
        $kit->name = $dadosKit['name'];
        $kit->description = $dadosKit['description'];
        $kit->ipvc_ref = $dadosKit['ipvc_ref'];
        $kit->price = $dadosKit['price'];
        $kit->price_day = $dadosKit['price_day'];
        $kit->quantity = $dadosKit['quantity'];
        $kit->image = $dadosKit['image_path'];
        $kit->save();

        foreach ($request->lia_codes as $index => $liaCode) {
            $kitUnity = new KitUnity();
            $kitUnity->lia_code = $liaCode;
            $kitUnity->kit_id = $kit->id;
            
           
            $definirEstadoKitUnity = 1; 

            
            if (isset($request->items_for_unity[$index]) && is_array($request->items_for_unity[$index])) {
                foreach ($request->items_for_unity[$index] as $itemId) {
                    $itemUnityTemp = ItemUnity::find($itemId);
                    if ($itemUnityTemp && $itemUnityTemp->item_unity_state_id == 2) {
                        $definirEstadoKitUnity = 2; 
                        break; 
                    }
                }
            }

            
            $kitUnity->kit_unity_state_id = $definirEstadoKitUnity; 
            $kitUnity->save();

            
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

    
    $request->session()->forget('dados_do_kit');

    return redirect()->route('kits.index')->with('toast_success', 'Kit e respetivas unidades gravados com sucesso!');
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


/*
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

*/
  


    /**
     * 2. ATUALIZAR LIA_CODE, ESTADO E ITENS DA UNIDADE
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $unidade = KitUnity::find($id);

        if (!$unidade) {
            return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
        }

        // Validação dos campos
        $request->validate([
            'lia_code'           => 'required|string|unique:kit_unity,lia_code,' . $id,
            'kit_unity_state_id' => 'required|in:1,2', // 1: Ativo (Visível), 2: Oculto
            'items_kept'         => 'nullable|array',   // IDs dos itens que o utilizador quer MANTER/REMOVER
        ], [
            'lia_code.required' => 'O código LIA é obrigatório.',
            'lia_code.unique'   => 'Este código LIA já está a ser utilizado por outra unidade.',
        ]);

        // 1. Atualiza os dados básicos da Unidade do Kit
        $unidade->lia_code = $request->lia_code;
        $unidade->kit_unity_state_id = $request->kit_unity_state_id;
        $unidade->save();

        // 2. GESTÃO DOS ITENS INTERNOS (Remover os desmarcados e associar novos)
        // IDs que vieram selecionados no modal
        $itemsSelected = $request->input('items_kept', []);

        // Remover a associação de todos os itens atuais que NÃO foram selecionados no modal
        ItemUnity::where('kit_unity_id', $unidade->id)
            ->whereNotIn('id', $itemsSelected)
            ->update(['kit_unity_id' => null]);

        // Associar os novos itens que foram marcados (caso fossem itens livres previamente)
        if (!empty($itemsSelected)) {
            ItemUnity::whereIn('id', $itemsSelected)
                ->update(['kit_unity_id' => $unidade->id]);
        }

        Alert::success('Sucesso', 'Unidade de kit atualizada com sucesso!');
        return redirect()->route('kits.show', $unidade->id);
    }

    /**
     * 3. ELIMINAR UNIDADE (Mudar estado para 3 - Anulado)
     */
   
    
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