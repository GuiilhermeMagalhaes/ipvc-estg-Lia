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

        // 1. Carregar a unidade atual, as peças lá de dentro, E os estados dessas peças
        $unidade = KitUnity::with(['kit', 'itemUnities.item', 'itemUnities.itemUnityState'])->find($id);

        if (!$unidade) {
            return redirect()->route('kits.index')->with('toast_error', 'Unidade de Kit não encontrada.');
        }

        // 2. Trazer TODAS as malas iguais a esta (sem filtrar o estado) para a lista de LIA Codes
        $unidadesDoKit = KitUnity::with('kitUnityState') // Carrega a relação de estado do Kit
                                 ->where('kit_id', $unidade->kit_id)
                                 ->get();

        // 3. Os Itens Livres (Para adicionar à mala, estes sim devem continuar filtrados para não juntares lixo/peças avariadas)
        $itensLivres = ItemUnity::with('item')
                                ->whereNull('kit_unity_id')
                                ->whereIn('item_unity_state_id', [1, 2]) 
                                ->get();

        return view('admin.kitUnities.show', [
            'unidade'       => $unidade,
            'kit'           => $unidade->kit,
            'unidadesDoKit' => $unidadesDoKit,
            'itensLivres'   => $itensLivres
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
    switch ($estadoId) {
        case 2:
            $nomesEstados[] = 'Oculto';
            break;
        case 3:
            $nomesEstados[] = 'Anulado';
            break;
        case 4:
            $nomesEstados[] = 'Em Manutenção';
            break;
        default:
            $nomesEstados[] = 'Inválido';
    }
}

                $listaEstados = implode(', ', $nomesEstados);
                
                return redirect()->back()->withInput()->with('toast_error', "Não é possível definir o kit como Ativo, porque o kit tem itens com o(s) estado(s): {$listaEstados}. Precisa de editar o item ou eliminá-lo do kit.");
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($unidade, $request, $itemsKept) {

        // 1. Tira da mala as peças que o técnico removeu no formulário
            \App\Models\ItemUnity::where('kit_unity_id', $unidade->id)
                ->whereNotIn('id', $itemsKept)
                ->update(['kit_unity_id' => null]);

                // 2. Coloca na mala as peças que o técnico manteve ou adicionou
            \App\Models\ItemUnity::whereIn('id', $itemsKept)
                ->update(['kit_unity_id' => $unidade->id]);
            
           
            // 3. AUTO-CURA: Verifica se, de todas as peças que FICARAM na mala, existe alguma estragada/emprestada
            $temPecaAvariada = \App\Models\ItemUnity::where('kit_unity_id', $unidade->id)
                ->where('item_unity_state_id', '!=', 1)
                ->exists();
    
            $estadoKit = $temPecaAvariada ? 2 : $request->input('kit_unity_state_id');
          
            $unidade->update([
                'lia_code' => $request->input('lia_code'),
                'kit_unity_state_id' => $estadoKit
            ]);
        });

        return redirect()->back()->with('toast_success', 'Unidade de kit atualizada! O estado da mala foi sincronizado automaticamente.');
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

    // 1. Guardamos o ID do Kit criado para podermos verificar os avisos a seguir
    $novoKitId = DB::transaction(function () use ($request, $dadosKit) {
        
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
        
        // Retornamos o ID para fora da transação
        return $kit->id;
    }); 

    // 2. Limpar a sessão
    $request->session()->forget('dados_do_kit');

    // 3. Verificar se ALGUMA das malas criadas ficou com o estado de avaria/oculto (diferente de 1)
    $temMalaOculta = \App\Models\KitUnity::where('kit_id', $novoKitId)
                    ->where('kit_unity_state_id', '!=', 1)
                    ->exists();

    // 4. Disparar o Toast apropriado
    if ($temMalaOculta) {
        return redirect()->route('kits.index')
            ->with('toast_warning', 'Kit guardado, mas ficará oculto pois contêm peças em manutenção/uso.');
    }

    return redirect()->route('kits.index')->with('toast_success', 'Kit e respetivas unidades gravados e disponíveis com sucesso!');
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

