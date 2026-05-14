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

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Captura o valor da pesquisa
            $search = $request->search;

            if (!empty($search)) {
            // Consulta os itens disponíveis conforme a pesquisa
            $itens = Item::where('item_state_id', '=', 1)
                ->where('nome', 'LIKE', '%' . $request->search . '%')
                ->orWhere('lia_code', 'LIKE', '%' . $request->search . '%')
                ->orWhere('ipvc_ref', 'LIKE', '%' . $request->search . '%')
                ->whereRaw("LOWER(nome) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();
            } else {
                $itens = Item::where('item_state_id', '=', 1)->get();
            }

            // Constrói o HTML para cada item encontrado
            if ($itens->count() > 0) {
                foreach ($itens as $item) {
                    $output .= '<div class="col-sm-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column justify-content-center text-center">
                                            <h5 class="card-title">' . htmlspecialchars($item->nome, ENT_QUOTES, 'UTF-8') . '</h5>
                                            <p class="card-text">' . htmlspecialchars($item->ipvc_ref, ENT_QUOTES, 'UTF-8') . '</p>
                                            <p class="card-text">' . number_format($item->preco, 2, ',', '.') . '€ / dia</p>
                                            <a class="btn btn-primary mx-auto" href="' . route('itens.show', ['id' => $item->id]) . '">VER DETALHES</a>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }else {
            $itens = Item::where('item_state_id', '=', 1)->get();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itens.index', ['itens' => $itens]);
        }
        return redirect('/');
    }

    public function searchItens(Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Captura o valor da pesquisa
            $search = $request->search;

            if (!empty($search)) {
                $itens = Item::where('nome', 'like', '%' . $search . '%')
                    ->orWhere('lia_code', 'like', '%' . $search . '%')
                    ->get();

            } else {
                // Se não houver pesquisa, retornar todos os itens
                $itens = Item::all();
            }

            $count = 0;
            // Constrói o HTML para cada item encontrado
            if ($itens->count() > 0) {
                foreach ($itens as $item) {

                    if ($item instanceof Item) {
                        // Adiciona o tipo "Item" à sugestão
                        $output .= '<div class="available-item d-flex justify-content-between align-items-center mb-2 p-2 bg-white border rounded">
                                    <span><strong>' . $item->nome . '</strong> <small class="text-muted">(' . $item->lia_code . ')</small></span>
                                    <button type="button" class="btn btn-primary btn-sm add-item-btn" data-id="' . $item->id . '" data-nome="' . $item->nome . '" data-code="' . $item->lia_code . '">Adicionar</button>
                                </div>';
                    }

                    $count++;
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }
    }

    public function ocultos(Request $request)
    {
        if ($request->ajax()) {
            $output = '';

            // Captura o valor da pesquisa
            $search = $request->search;

            if (!empty($search)) {
            // Consulta os itens disponíveis conforme a pesquisa
            $itens = Item::where('item_state_id', '!=', 1)
                ->where('nome', 'LIKE', '%' . $request->search . '%')
                ->orWhere('lia_code', 'LIKE', '%' . $request->search . '%')
                ->orWhere('ipvc_ref', 'LIKE', '%' . $request->search . '%')
                ->whereRaw("LOWER(nome) LIKE ?", ['%' . strtolower(request('search')) . '%'])
                ->get();
            } else {
                $itens = Item::where('item_state_id', '!=', 1)->get();
            }

            // Constrói o HTML para cada item encontrado
            if ($itens->count() > 0) {
                foreach ($itens as $item) {
                    $output .= '<div class="col-sm-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column justify-content-center text-center">
                                            <h5 class="card-title">' . htmlspecialchars($item->nome, ENT_QUOTES, 'UTF-8') . '</h5>
                                            <p class="card-text">' . htmlspecialchars($item->ipvc_ref, ENT_QUOTES, 'UTF-8') . '</p>
                                            <p class="card-text">' . number_format($item->preco, 2, ',', '.') . '€ / dia</p>
                                            <a class="btn btn-primary mx-auto" href="' . route('itens.show', ['id' => $item->id]) . '">VER DETALHES</a>
                                        </div>
                                    </div>
                                </div>';
                }
            } else {
                $output = '<p>Nenhum item encontrado.</p>';
            }

            return response()->json($output);
        }else {
            $itens = Item::where('item_state_id', '!=', 1)->get();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itens.ocultos', ['itens' => $itens]);
        }
        return redirect('/');
    }

    public function create()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itens.create', [
                'categorias' => ItemCategorie::all(),
                'itens' => Item::where('item_state_id', '=', 1)->where('kit_id', null)->get()
            ]);
        }
        return redirect('/');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'nome' => 'required',
                'model' => 'required',
                'preco' => 'required',
                'lia_code' => 'required|unique:item,lia_code'
            ],
            [
                'nome.required' => 'O item deve ter um nome',
                'model.required' => 'O item deve ter um modelo',
                'preco.required' => 'O item deve ter um preço associado',
                'lia_code.required' => 'O item deve ter um código LIA associado',
                'lia_code.unique' => 'O código LIA deve ser único'
            ]
        );

        if ($request->image != null) {
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
        } else {
            $path = "images/empty.png";
        }

        Item::create([
            'ipvc_ref' => $request->ipvc_ref,
            'serial_number' => $request->serial_number,
            'nome' => $request->nome,
            'model' => $request->model,
            'lia_code' => $request->lia_code,
            'observation' => $request->observation,
            'acessorio' => $request->acessorio,
            'preco' => $request->preco,
            'categoria_id' => $request->categoria_id,
            'item_state_id' => $request->item_state_id,
            'image' => $path
        ]);

        return redirect('admin/itens')->with('toast_success', 'Item criado com sucesso!');
    }

    public function show($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itens.show', [
                'item' => Item::find($id),
                'categoria' => ItemCategorie::all()
            ]);
        }
        return redirect('/');
    }

    public function destroy($id)
    {
        $itensReserva = ItemReserve::where('item_id', $id)->get();
        if ($itensReserva->isEmpty()) {
            $item = Item::find($id);
            $item->delete();
        } else {
            return redirect()->to('/admin/itens/')->with('toast_error', 'Existe uma reserva com este item!');
        }
        return redirect('/admin/itens');
    }

    public function edit($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.itens.edit', [
                'item' => Item::find($id),
                'kit' => Kit::all(),
                'categorias' => ItemCategorie::all()
            ]);
        }
        return redirect('/');
    }

    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        $request->validate(
            [
                'nome' => 'required',
                'model' => 'required',
                'lia_code' => ['required', Rule::unique('item', 'lia_code')->ignore($id)],
                'preco' => 'required'
            ],
            [
                'nome.required' => 'O item deve ter um nome',
                'model.required' => 'O item deve ter um modelo',
                'lia_code.required' => 'O item deve ter um código LIA associado',
                'lia_code.unique' => 'Código LIA deve ser único',
                'preco.required' => 'O item deve ter um preço associado'
            ]
        );

        if ($request->image != null) {
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/itens', $imageName, 'public');
        } else {
            $path = $item->image;
        }

        $item->update([
            'ipvc_ref' => $request->ipvc_ref,
            'serial_number' => $request->serial_number,
            'lia_code' => $request->lia_code,
            'nome' => $request->nome,
            'model' => $request->model,
            'preco' => $request->preco,
            'observation' => $request->observation,
            'acessorio' => $request->acessorio,
            'categoria_id' => $request->categoria_id,
            'item_state_id' => $request->item_state_id,
            'image' => $path
        ]);

        $item->save();

        return redirect(route('itens.show', $item->id));
    }
}
