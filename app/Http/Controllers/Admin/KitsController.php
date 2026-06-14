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
                                        
                                        <a class="btn btn-primary mx-auto" style="width: 140px;" href="' . route('kits.show', ['id' => $unidade->id]) . '">VER DETALHES</a>
                                    </div>
                                </div>
                            </div>';
                } 
            } else {
                $output = '<div class="col-12"><p class="text-muted text-center">Nenhuma unidade encontrada.</p></div>';
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
    if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
        return redirect('/');
    }

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
    else {
                $output = '<div class="col-12"><p class="text-muted text-center">Nenhuma unidade oculta encontrada.</p></div>';
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

        $unidadesDoKit = KitUnity::where('kit_id', $unidade->kit_id)
        ->whereIn('kit_unity_state_id', [1, 2])
        ->get();

        $itensLivres = ItemUnity::with('item')->whereNull('kit_unity_id')->get();

        return view('admin.kitUnities.show', [
            'unidade'     => $unidade,
            'kit'         => $unidade->kit,
            'unidadesDoKit' => $unidadesDoKit,
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
        
        if (!$unidade) {
            return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
        }

        
        $itemsKept = $request->input('items_kept', []);

       
        if (count($itemsKept) === 0) {
            return redirect()->back()->with('toast_error', 'Erro: O kit não pode ficar sem nenhum item associado.');
        }

        
        if ($request->input('kit_unity_state_id') == 1) {
            
            
            $invalidItemsStates = \App\Models\ItemUnity::whereIn('id', $itemsKept)
                ->whereIn('item_unity_state_id', [2, 3, 4])
                ->pluck('item_unity_state_id')
                ->unique()
                ->toArray();

            if (!empty($invalidItemsStates)) {
                $nomesEstados = [];
                foreach ($invalidItemsStates as $estadoId) {
                    $nomesEstados[] = match($estadoId) {
                        2 => 'Oculto',
                        3 => 'Anulado',
                        4 => 'Em Manutenção',
                        default => 'Inválido'
                    };
                }

                $listaEstados = implode(', ', $nomesEstados);
                
                return redirect()->back()->withInput()->with('toast_error', "Não é possível definir o kit como Ativo, porque o kit tem itens com o(s) estado(s): {$listaEstados}. Precisa de editar o item ou eliminá-lo do kit.");
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($unidade, $request, $itemsKept) {
            
           
            $hasHiddenItem = \App\Models\ItemUnity::whereIn('id', $itemsKept)
                ->where('item_unity_state_id', 2)
                ->exists();

            
            $estadoKit = $hasHiddenItem ? 2 : $request->input('kit_unity_state_id');

          
            $unidade->update([
                'lia_code' => $request->input('lia_code'),
                'kit_unity_state_id' => $estadoKit
            ]);

            
            \App\Models\ItemUnity::where('kit_unity_id', $unidade->id)
                ->whereNotIn('id', $itemsKept)
                ->update(['kit_unity_id' => null]);

            
            \App\Models\ItemUnity::whereIn('id', $itemsKept)
                ->update(['kit_unity_id' => $unidade->id]);
        });

        return redirect()->back()->with('toast_success', 'Unidade de kit e componentes atualizados com sucesso!');
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
    
        
       \Illuminate\Support\Facades\DB::transaction(function () use ($unidade) {
        
       
        $unidade->update([
            'kit_unity_state_id' => 3
        ]);

       
        if ($unidade->kit) {
            $unidade->kit->decrement('quantity', 1);
        }

        
        ItemUnity::where('kit_unity_id', $unidade->id)->update([
            'kit_unity_id' => null
        ]);
    });

    return redirect()->route('kits.index')->with('toast_success', 'Unidade anulada e stock atualizado com sucesso!');
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
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:1100',
            'price' => 'required|numeric|min:0',
            'price_day' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'ipvc_ref'    => 'nullable|string|max:190',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

        ],
        [
            'name.required'        => 'O kit deve ter um nome.',
            'name.string'          => 'O nome deve ser um texto válido.',
            'name.max'             => 'O nome não pode ter mais de 190 caracteres.',

            'description.required' => 'O kit deve ter uma descrição.',
            'description.string' => 'A descrição deve ser um texto válido.',
            'description.max' => 'A descrição não pode ter mais de 1100 caracteres.',

            'price.required'       => 'O kit deve ter um preço associado.', 
            'price.numeric'        => 'O preço deve ser um número válido.',
            'price.min'            => 'O preço não pode ser inferior a 0.',
           

            'price_day.required'   => 'O kit deve ter um preço por dia associado.',
            'price_day.numeric'    => 'O preço por dia deve ser um número válido.',
            'price_day.min'        => 'O preço por dia não pode ser inferior a 0 €.',


            'quantity.required'    => 'Insira a quantidade total.',
            'quantity.integer'     => 'A quantidade total deve ser um número inteiro.',
            'quantity.min'         => 'A quantidade total deve ser pelo menos 1.',

            'ipvc_ref.string'      => 'O número de série deve ser um texto válido.',
            'ipvc_ref.max'         => 'O número de série não pode ter mais de 190 caracteres.',


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
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        $kit = Kit::find($id);

        if (!$kit) {
            return redirect()->route('kits.index')->with('toast_error', 'Kit não encontrado.');
        }

        return view('admin.kits.edit', [
            'kit' => $kit,
            'categorias' => ItemCategorie::all()
        ]);
    }
    return redirect('/');
}


public function update(Request $request, $id)
{
  
    if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
        return redirect('/');
    }

  
    $kit = Kit::find($id);

    if (!$kit) {
        return redirect()->route('kits.index')->with('toast_error', 'Kit não encontrado.');
    }

    
    $request->validate([

        'name' => 'required|string|max:190',
        'description' => 'required|string|max:1100',
        'price' => 'required|numeric|min:0',
        'price_day' => 'required|numeric|min:0',
        'ipvc_ref'    => 'nullable|string|max:190',
        'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

    ], [
       
        'name.required'        => 'O kit deve ter um nome.',
            'name.string'          => 'O nome deve ser um texto válido.',
            'name.max'             => 'O nome não pode ter mais de 190 caracteres.',

            'description.required' => 'O kit deve ter uma descrição.',
            'description.string' => 'A descrição deve ser um texto válido.',
            'description.max' => 'A descrição não pode ter mais de 1100 caracteres.',

            'price.required'       => 'O kit deve ter um preço associado.', 
            'price.numeric'        => 'O preço deve ser um número válido.',
            'price.min'            => 'O preço não pode ser inferior a 0.',
           

            'price_day.required'   => 'O kit deve ter um preço por dia associado.',
            'price_day.numeric'    => 'O preço por dia deve ser um número válido.',
            'price_day.min'        => 'O preço por dia não pode ser inferior a 0 €.',

            'ipvc_ref.string'      => 'O número de série deve ser um texto válido.',
            'ipvc_ref.max'         => 'O número de série não pode ter mais de 190 caracteres.',


            'image.image'          => 'O ficheiro selecionado deve ser uma imagem.',
            'image.mimes'          => 'A imagem deve ser do formato: jpeg, png, jpg ou webp.',
            'image.max'            => 'A imagem não pode ter mais de 2MB.',
        


    ]);

    
    if ($request->hasFile('image')) {
        if ($kit->image && Storage::disk('public')->exists($kit->image)) {
            Storage::disk('public')->delete($kit->image);
        }
        $kit->image = $request->file('image')->store('kits', 'public');
    }

    
    $kit->name = $request->input('name');
    $kit->description = $request->input('description');
    $kit->ipvc_ref = $request->input('ipvc_ref');
    $kit->price = $request->input('price');
    $kit->price_day = $request->input('price_day');
    
    $kit->save();

    
    return redirect()->route('kits.index')
        ->with('toast_success', 'Kit atualizado com sucesso!');
   
}


}

/*
public function update(Request $request, $id)
{
   
    if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
        return redirect('/');
    }

   
    $kit = Kit::find($id);

    if (!$kit) {
        return redirect()->route('kits.index')->with('toast_error', 'Kit não encontrado.');
    }

   l
    $request->validate([
        'name'        => 'required|string|max:190',
        'description' => 'nullable|string',
        'ipvc_ref'    => 'string|max:255',
        'price'       => 'required|numeric|min:0',     
        'price_day'   => 'required|numeric|min:0',      
        'quantity'    => 'required|integer|min:' . $kit->quantity, 
        'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

    ], [
       
        'name.required'        => 'O nome do kit é obrigatório.',
        'name.string'          => 'O nome do kit deve ser um texto válido.',
        'name.max'             => 'O nome do kit não pode ter mais de 190 caracteres.',
        
        'ipvc_ref.string'      => 'A referência IPVC deve ser um texto válido.',
        'ipvc_ref.max'         => 'A referência IPVC não pode ter mais de 255 caracteres.',
        
        'price.required'       => 'O preço base é obrigatório.',
        'price.numeric'        => 'O preço base deve ser um valor numérico.',
        'price.min'            => 'O preço base não pode ser um valor negativo.',
        
        'price_day.required'   => 'O preço por dia é obrigatório.',
        'price_day.numeric'    => 'O preço por dia deve ser um valor numérico.',
        'price_day.min'        => 'O preço por dia não pode ser um valor negativo.',
        
        'quantity.required'    => 'A quantidade total é obrigatória.',
        'quantity.integer'     => 'A quantidade deve ser um número inteiro.',
        'quantity.min'         => 'Não é permitido diminuir a quantidade total de unidades já existentes do Kit (' . $kit->quantity . ').',
        
        'image.image'          => 'O ficheiro selecionado deve ser uma imagem válida.',
        'image.mimes'          => 'A imagem deve ser do formato: jpeg, png, jpg ou webp.',
        'image.max'            => 'A imagem não pode ter um tamanho superior a 2MB.',
        


    ]);

    
    $imagePath = $kit->image; 
    if ($request->hasFile('image')) {
        
        $imagePath = $request->file('image')->store('kits', 'public');
    }

   
   
    $dadosGerais = $request->except(['image']);
    $dadosGerais['image'] = $imagePath;

    session(['dados_kit_edicao' => $dadosGerais]);

    return redirect()->route('kits.unitiesEtapa', $kit->id);
   
}


  public function showKitUnitiesEtapa($id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        $kit = Kit::find($id);
        $dadosKit = session('dados_kit_edicao');

        
        if (!$kit || !$dadosKit) {
            return redirect()->route('kits.edit', $id)->with('toast_error', 'Sessão expirada ou kit inválido.');
        }

        
        $unidadesAtuais = KitUnity::where('kit_id', $kit->id)->get();
        
        
        $novasUnidadesQtd = $dadosKit['quantity'] - $unidadesAtuais->count();

    
        $itensLivres = ItemUnity::with('item')
            ->whereNull('kit_unity_id')
            ->whereIn('item_unity_state_id', [1, 2]) 
            ->get();

        
        return view('admin.kitUnities.edit', [
            'kit' => $kit,
            'unidadesAtuais' => $unidadesAtuais,
            'novasUnidadesQtd' => $novasUnidadesQtd,
            'itensLivres' => $itensLivres // <-- IMPORTANTE
        ]);
    }
    return redirect('/');
}


public function updateKitUnitiesEtapa(Request $request, $id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        $kit = Kit::find($id);

        if (!$kit) {
            return redirect()->route('kits.index')->with('toast_error', 'Kit não encontrado.');
        }

        
        $request->validate([
            'lias_atuais'              => 'required|array',
            'lias_atuais.*'            => 'required|string|distinct',
            'data_aquisicao_atuais'    => 'array',
            'data_aquisicao_atuais.*'  => 'nullable|date|before_or_equal:today',
            
            'novos_lias'               => 'sometimes|array',
            'novos_lias.*'             => 'required|string|distinct|unique:kit_unities,lia_code',
            'data_aquisicao_novas'     => 'sometimes|array',
            'data_aquisicao_novas.*'   => 'nullable|date|before_or_equal:today',
        ], [
            'lias_atuais.*.required'             => 'O código LIA não pode ficar vazio.',
            'lias_atuais.*.distinct'             => 'Inseriu códigos LIA duplicados entre as unidades atuais.',
            'data_aquisicao_atuais.*.date'       => 'Insira uma data de aquisição válida.',
            'data_aquisicao_atuais.*.before_or_equal' => 'A data de aquisição não pode ser no futuro.',
            
            'novos_lias.*.required'              => 'Deve preencher o código LIA para as novas unidades do kit.',
            'novos_lias.*.unique'                => 'Este código LIA já existe noutra unidade no sistema.',
            'novos_lias.*.distinct'              => 'Inseriu códigos LIA duplicados entre as novas unidades.',
            'data_aquisicao_novas.*.date'        => 'Insira uma data de aquisição válida.',
            'data_aquisicao_novas.*.before_or_equal' => 'A data de aquisição não pode ser no futuro.',
        ]);

       
        if ($request->has('novos_lias')) {
            foreach ($request->novos_lias as $novoLia) {
                if (in_array($novoLia, $request->lias_atuais)) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['novos_lias' => 'Inseriu códigos LIA duplicados entre os existentes e os novos.']);
                }
            }
        }

       
        foreach ($request->lias_atuais as $unityId => $liaCode) {
            $existeNoutro = KitUnity::where('lia_code', $liaCode)
                                    ->where('id', '!=', $unityId) // ignora a própria linha
                                    ->exists();
            if ($existeNoutro) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['lias_atuais.'.$unityId => 'Este código LIA já está registado noutra unidade de kit no sistema.']);
            }
        }

        $dadosKit = session('dados_kit_edicao');

        if (!$dadosKit) {
            return redirect()->route('kits.edit', $id)->with('toast_error', 'Sessão expirada. Por favor tente novamente.');
        }

       
        $kit->update([
            'name'         => $dadosKit['name'],
            'description'  => $dadosKit['description'] ?? null,
            'ipvc_ref'     => $dadosKit['ipvc_ref'] ?? null,
            'lia_code'     => $dadosKit['lia_code'] ?? null, 
            'price'        => $dadosKit['price'],
            'price_day'    => $dadosKit['price_day'] ?? null,
            'quantity'     => $dadosKit['quantity'],
            'image'        => $dadosKit['image'] ?? $kit->image, 
        ]);

      
        if ($request->has('lias_atuais')) {
            foreach ($request->lias_atuais as $unityId => $liaCode) {
                $unity = KitUnity::find($unityId);
                if ($unity) {
                    $unity->update([
                        'lia_code'       => $liaCode,
                        'data_aquisicao' => $request->data_aquisicao_atuais[$unityId] ?? null
                    ]);
                }
            }
        }

        
        if ($request->has('novos_lias')) {
            foreach ($request->novos_lias as $index => $novoLia) {
                KitUnity::create([
                    'kit_id'             => $kit->id,
                    'lia_code'           => $novoLia,
                    'data_aquisicao'     => $request->data_aquisicao_novas[$index] ?? null,
                    'kit_unity_state_id' => 1 
                ]);
            }
        }

        
        session()->forget('dados_kit_edicao');

        return redirect()->route('kits.index')->with('toast_success', 'Kit e as suas respetivas unidades gravados com sucesso!');
    }
    return redirect('/');
}
*/
  







/*
    public function update(Request $request, $id)
    {
        if (Auth::user()->user_type_id != 1 && Auth::user()->user_type_id != 2) {
            return redirect('/');
        }

        $unidade = KitUnity::find($id);

        if (!$unidade) {
            return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
        }

       
        $request->validate([
            'lia_code'           => 'required|string|unique:kit_unity,lia_code,' . $id,
            'kit_unity_state_id' => 'required|in:1,2',
            'items_kept'         => 'nullable|array',  
        ], [
            'lia_code.required' => 'O código LIA é obrigatório.',
            'lia_code.unique'   => 'Este código LIA já está a ser utilizado por outra unidade.',
        ]);

       
        $unidade->lia_code = $request->lia_code;
        $unidade->kit_unity_state_id = $request->kit_unity_state_id;
        $unidade->save();

       
       
        $itemsSelected = $request->input('items_kept', []);

       
        ItemUnity::where('kit_unity_id', $unidade->id)
            ->whereNotIn('id', $itemsSelected)
            ->update(['kit_unity_id' => null]);

        
        if (!empty($itemsSelected)) {
            ItemUnity::whereIn('id', $itemsSelected)
                ->update(['kit_unity_id' => $unidade->id]);
        }

        Alert::success('Sucesso', 'Unidade de kit atualizada com sucesso!');
        return redirect()->route('kits.show', $unidade->id);
    }
*/


   