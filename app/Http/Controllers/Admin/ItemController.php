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


        $query = ItemUnity::with('item')->where('item_unity_state_id', 1)->whereHas('item');


        if (!empty($search)) {
            $query->where(function($mainQuery) use ($search) {

                $mainQuery->where('lia_code', 'LIKE', '%' . $search . '%')
                
                ->orWhereHas('item', function($q) use ($search) {
                    $q->where('nome', 'LIKE', '%' . $search . '%')
                    ->orWhere('model', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $unidades = $query->get();

        if ($unidades->count() > 0) {
            foreach ($unidades as $unidade) {
                if (!$unidade->item) {
                    continue; 
                }
                $output .= '<div class="col-sm-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column justify-content-center text-center">
                                        <h1 class="card-title">' . htmlspecialchars($unidade->item->nome, ENT_QUOTES, 'UTF-8') . '</h1>
                                        <small class="text-muted mb-2">LIA: ' . htmlspecialchars($unidade->lia_code, ENT_QUOTES, 'UTF-8') . '</small>
                                        <p class="card-text">' . htmlspecialchars($unidade->item->ipvc_ref, ENT_QUOTES, 'UTF-8') . '</p>
                                        <p class="card-text card-text-preco">' . number_format($unidade->item->price_day, 2, ',', '.') . ' € / dia</p>
                                        <a class="btn btn-primary mx-auto" style="width: 140px;" href="' . route('itens.show', ['id' => $unidade->id]) . '">VER DETALHES</a>
                                    </div>
                                </div>
                            </div>';
            }
        } else {
            $output = '<p>Nenhuma unidade encontrada.</p>';
        }

        return response()->json($output);
        }

        else {
            
            $unidades = ItemUnity::with('item')->where('item_unity_state_id', 1)->get();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            
            return view('admin.itemUnities.index', ['unidades' => $unidades]);
        }
        return redirect('/');
    }

    

        public function show($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
           
            //$unidade = ItemUnity::with(['item', 'itemUnityState'])->find($id);
            $unidade = ItemUnity::with(['item.itemCategorie', 'itemUnityState'])->find($id);
            if (!$unidade) {
                return redirect()->route('itens.index')->with('toast_error', 'Unidade não encontrada.');
            }

            $unidadesDoItem = ItemUnity::where('item_id', $unidade->item_id)
                                        ->whereIn('item_unity_state_id', [1, 2])
                                        ->get();

            return view('admin.itemUnities.show', [
                'unidade'   => $unidade,
                'item'      => $unidade->item, 
                'unidadesDoItem' => $unidadesDoItem
            ]);
        }
        return redirect('/');
    }


    public function updateUnity(Request $request, $id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            
            
            $request->validate([
                'lia_code' => 'required|string|unique:item_unity,lia_code,' . $id,
                'item_unity_state_id' => 'required|exists:item_unity_states,id',
                'data_aquisicao' => 'nullable|date|before_or_equal:today',
            ], [
                'lia_code.required' => 'O código LIA não pode ficar vazio.',
                'lia_code.unique' => 'Este código LIA já está a ser usado noutra unidade.',
                'data_aquisicao.date' => 'O formato da data de aquisição é inválido.',
                'data_aquisicao.before_or_equal' => 'A data de aquisição não pode ser uma data futura.',
            ]);

            $unidade = ItemUnity::with('kitUnity.kit')->find($id);
            
            if ($unidade) {
                
                $unidade->update($request->only(['lia_code', 'item_unity_state_id', 'data_aquisicao']));

              
          if ($unidade->item_unity_state_id == 2 && 
                $unidade->kitUnity && 
                $unidade->kitUnity->kit_unity_state_id != 2) {
                
               
                $unidade->kitUnity->kit_unity_state_id = 2;
                $unidade->kitUnity->save();
            }

            
                return redirect()->route('itens.show', $id)->with('toast_success', 'Unidade atualizada com sucesso!');
            }

            return redirect()->back()->with('toast_error', 'Unidade não encontrada.');
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

            if ($unidade->item_unity_state_id == 3) {
            return redirect()->route('itens.index')->with('toast_error', 'Esta unidade já se encontra anulada.');
            }

           
            \Illuminate\Support\Facades\DB::transaction(function () use ($unidade) {
               
                $unidade->update([
                    'item_unity_state_id' => 3 
                ]);

                if ($unidade->kitUnity) {
                if ($unidade->kitUnity->kit_unity_state_id != 2) {
                    $unidade->kitUnity->update([
                        'kit_unity_state_id' => 2
                    ]);
                }
            }

                $item = $unidade->item;
                if ($item) {
                    $item->decrement('quantity', 1);
                }
            });

            return redirect()->route('itens.index')->with('toast_success', 'Unidade anulada e stock atualizado com sucesso!');
        }
        return redirect('/');
    }



    public function create()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itens.create', [
                'categorias' => ItemCategorie::all(),
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
                    'categoria_id'  => 'required|exists:item_categories,id',
                    'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                
                ],
                [
                    'nome.required' => 'O item deve ter um nome',
                    'model.required' => 'O item deve ter um modelo',
                    'preco.required' => 'O item deve ter um preço associado',
                    'price_day.required'     => 'O item deve ter um preço por dia associado.',
                    'quantity.required'      => 'Insira a quantidade total.',

                    'quantity.integer'       => 'A quantidade total deve ser um número inteiro.',
                    
                    'preco.min'              => 'O preço não pode ser inferior a 0.',
                    'price_day.min'          => 'O preço por dia não pode ser inferior a 0.',
                    'quantity.min'           => 'A quantidade total deve ser pelo menos 1.',
                    'categoria_id.exists'   => 'A categoria selecionada é inválida.', 
                    'image.image'           => 'O ficheiro selecionado deve ser uma imagem.',
                    'image.mimes'           => 'A imagem deve ser do formato: jpeg, png, jpg ou webp.',
                    'image.max'             => 'A imagem não pode ter mais de 2MB.',

                    
                ]
            );

            if ($request->image != null) {
                $imagePath = $request->file('image');
                $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
                $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
            } else {
                $path = "images/empty.png";
            }

<<<<<<< HEAD
     
            $itemData = $request->only(['ipvc_ref', 'serial_number', 'nome', 'model', 'observation', 'acessorio', 'preco', 'categoria_id', 'price_day', 'quantity']);
            $itemData['image'] = $path; 
            $itemData['item_state_id'] = 1; // Visível

            
            return redirect()->route('itens.createUnities')->with([ 
=======
               
            $itemData = $request->only(['ipvc_ref', 'serial_number', 'nome', 'model', 'observation', 'acessorio', 'preco', 'categoria_id', 'price_day', 'quantity']);
            $itemData['image'] = $path; 

            
            return redirect()->route('itens.createUnities')->with([
>>>>>>> b3e639c2da85771936a794cc4a8f457b8aef38c7
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

            if (!$itemData || !$quantity || !$item_nome) {
                return redirect()->route('itens.create')->with('toast_error', 'Por favor, preencha os dados do item primeiro.');
            }

            //session()->keep(['item_data', 'item_nome', 'quantity']);
            
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
           

            /*if ($request->validator && $request->validator->fails()) {
            session()->reflash();
        }*/

        session()->keep(['item_data', 'item_nome', 'quantity']);
        $itemData = session('item_data');

            if (!$itemData) {
                return redirect()->route('itens.create')->with('toast_error', 'Sessão expirada. Volte a preencher os dados do item.');
            }

    
            $request->validate([
                'lia_codes'  => 'required|array',
                'lia_codes.*'=> 'required|string|distinct|unique:item_unity,lia_code',
                'data_aquisicao' => 'array',
                'data_aquisicao.*'   => 'nullable|date|before_or_equal:today',
            ], [
                'lia_codes.*.required' => 'O código LIA é obrigatório.',
                'lia_codes.*.unique'   => 'Este código LIA já existe no sistema.',
                'lia_codes.*.distinct' => 'Inseriu códigos LIA duplicados.',
                'data_aquisicao.*.date'       => 'Insira uma data válida.',
                'data_aquisicao.*.before_or_equal' => 'A data de aquisição não pode ser no futuro.',

            ]);

            
            \Illuminate\Support\Facades\DB::transaction(function () use ($itemData, $request) {
                
               
                $item = Item::create($itemData);

                
                foreach ($request->lia_codes as $index => $code) {
                    ItemUnity::create([
                        'lia_code'            => $code,
                        'data_aquisicao'      => $request->data_aquisicao[$index] ?? null,
                        //'data_aquisicao'      => $request->data_aquisicao,
                        'item_id'             => $item->id,
                        'kit_unity_id'        => null,
                        'item_unity_state_id' => 1 
                    ]);
                }
            });

            return redirect('admin/item-unities')->with('toast_success', 'Item criado com sucesso!');
        }
        return redirect('/');
    }



    public function ocultos(Request $request)
    {
       
        if ($request->ajax()) {
            $output = '';
            $search = $request->search;

           
            $query = ItemUnity::with('item')->where('item_unity_state_id', 2);

            if (!empty($search)) {
            $query->where(function($q) use ($search) {
               
                $q->where('lia_code', 'LIKE', '%' . $search . '%')
                
               
                ->orWhereHas('item', function($subQuery) use ($search) {
                    $subQuery->where('nome', 'LIKE', '%' . $search . '%')
                             ->orWhere('model', 'LIKE', '%' . $search . '%');
                });
            });
        }

            $unidades = $query->get();

            if ($unidades->count() > 0) {
                foreach ($unidades as $unidade) {
                    
                    $output .= '<div class="col-sm-3 mb-4">
                                    <div class="card h-100"> 
                                        <div class="card-body d-flex flex-column justify-content-center text-center">
                                            <h1 class="card-title">' . htmlspecialchars($unidade->item->nome, ENT_QUOTES, 'UTF-8') . '</h1>
                                            <small class="text-muted mb-2">Ref: ' . htmlspecialchars($unidade->item->ipvc_ref, ENT_QUOTES, 'UTF-8') . '</small>
                                            <p class="text-muted mb-2">LIA: ' . htmlspecialchars($unidade->lia_code, ENT_QUOTES, 'UTF-8') . '</p>
                                            <p class="card-text card-text-preco">' . number_format($unidade->item->preco, 2, ',', '.') . ' € / dia</p>
                                            <a class="btn btn-primary mx-auto" style="width: 140px;" href="' . route('itens.show', ['id' => $unidade->id]) . '">VER DETALHES</a>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhuma unidade oculta encontrada.</p>';
            }

            return response()->json($output);
        } 
        
       
        else {
            
            $unidades = ItemUnity::with('item')->where('item_unity_state_id', 2)->get();
        }

        
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itemUnities.ocultos', ['unidades' => $unidades]);
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

        

        $path = $item->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
        }

        $dadosItem = $request->except(['image']);
        $dadosItem['image'] = $path; 
        
        
        session(['dados_item_edicao' => $dadosItem]);

<<<<<<< HEAD
        return redirect()->route('itens.createUnitiesEtapa', ['id' => $item->id]);
=======
        
        return redirect()->route('itens.createUnitiesEtapa', $item->id);
>>>>>>> b3e639c2da85771936a794cc4a8f457b8aef38c7
    }
    return redirect('/');
}


public function showUnitiesEtapa($id)
{
    if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
        $item = Item::find($id);
        $dadosItem = session('dados_item_edicao');

        if (!$item || !$dadosItem) {
            return redirect()->route('itens.edit', $id)->with('toast_error', 'Sessão expirada ou item inválido.');
        }

        $unidadesAtuais = ItemUnity::where('item_id', $item->id)->get();
        
        $novasUnidadesQtd = $dadosItem['quantity'] - $unidadesAtuais->count();

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

           
            $request->validate([
                'lias_atuais'              => 'required|array',
               
                'lias_atuais.*'            => 'required|string|distinct',
                'data_aquisicao_atuais'    => 'array',
                'data_aquisicao_atuais.*'  => 'nullable|date|before_or_equal:today',
                
                'novos_lias'               => 'sometimes|array',
               
                'novos_lias.*'             => 'required|string|distinct|unique:item_unity,lia_code',
                'data_aquisicao_novas'     => 'sometimes|array',
                'data_aquisicao_novas.*'   => 'nullable|date|before_or_equal:today',

                
            ], [
                'lias_atuais.*.required'             => 'O código LIA não pode ficar vazio.',
                'lias_atuais.*.distinct'             => 'Inseriu códigos LIA duplicados entre as unidades atuais.',
                'data_aquisicao_atuais.*.date'       => 'Insira uma data de aquisição válida.',
                'data_aquisicao_atuais.*.before_or_equal' => 'A data de aquisição não pode ser no futuro.',
                'novos_lias.*.required'              => 'Deves preencher o código LIA para as novas unidades.',
                'novos_lias.*.unique'                => 'Este código LIA já existe no sistema.',
                'novos_lias.*.distinct'              => 'Inseriu códigos LIA duplicados entre as novas unidades.',
                'data_aquisicao_novas.*.date'        => 'Insira uma data de aquisição válida.',
                'data_aquisicao_novas.*.before_or_equal' => 'A data de aquisição não pode ser no futuro.',
            ]);



                        if ($request->has('novos_lias')) {
                foreach ($request->novos_lias as $novoLia) {
                    if (in_array($novoLia, $request->lias_atuais)) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['novos_lias' => 'Inseriu códigos LIA duplicados.']);
                    }
                }
            }

           
            foreach ($request->lias_atuais as $unityId => $liaCode) {
                $existeNoutro = \App\Models\ItemUnity::where('lia_code', $liaCode)
                                    ->where('id', '!=', $unityId) 
                                    ->exists();
                if ($existeNoutro) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['lias_atuais.'.$unityId => 'Este código LIA já está registado noutro item do sistema.']);
                }
            }


           
            $dadosItem = session('dados_item_edicao');

            if (!$dadosItem) {
                return redirect()->route('itens.edit', $id)->with('toast_error', 'Sessão expirada. Por favor tente novamente.');
            }

            
            $item->update([
                'nome' => $dadosItem['nome'],
                'model' => $dadosItem['model'],
                'ipvc_ref' => $dadosItem['ipvc_ref'] ?? null,
                'serial_number' => $dadosItem['serial_number'] ?? null,
                'preco' => $dadosItem['preco'],
                'price_day' => $dadosItem['price_day'] ?? null,
                'quantity' => $dadosItem['quantity'],
                'observation' => $dadosItem['observation'] ?? null,
                'acessorio' => $dadosItem['acessorio'] ?? null,
                'image' => $dadosItem['image'],
                'categoria_id' => $dadosItem['categoria_id']
            ]);


            if ($request->has('lias_atuais')) {
            foreach ($request->lias_atuais as $unityId => $liaCode) {
                $unity = ItemUnity::find($unityId);
                if ($unity) {
                    $unity->update([
                        'lia_code'       => $liaCode,
                        'data_aquisicao' => $request->data_aquisicao_atuais[$unityId] ?? null 
                    ]);
                }
            }
        }

        // Criar as novas unidades físicas 
        if ($request->has('novos_lias')) {
            foreach ($request->novos_lias as $index => $novoLia) {
                ItemUnity::create([
                    'item_id'             => $item->id,
                    'lia_code'            => $novoLia,
                    'data_aquisicao'      => $request->data_aquisicao_novas[$index] ?? null, 
                    'item_unity_state_id' => 1 
                ]);
            }
        }
           
            // Limpa a sessão 
            session()->forget('dados_item_edicao');

            return redirect()->route('itens.index')->with('toast_success', 'Item e unidades gravados com sucesso!');
        }
        return redirect('/');
    }

    public function manutencao()
    {
    $unidades = \App\Models\ItemUnity::where('item_unity_state_id', 4)
        ->with('item')
        ->get();

    return view('admin.ItemUnities.manutencao', ['unidades' => $unidades]);
    }




  



}

