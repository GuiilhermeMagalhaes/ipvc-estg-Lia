<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kit;
use App\Models\ItemCategorie;
use App\Models\KitReserve;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class KitsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $output = '';
            $search = $request->search;

            if (!empty($search)) {
                // Usamos Kit::where para iniciar uma Query SQL correta
                $kits = Kit::where('name', 'LIKE', '%' . $search . '%')
                    ->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($search) . '%'])
                    ->get();
            } else {
                // Se não há pesquisa, traz todos os registos de forma limpa
                $kits = Kit::all();
            }

            if ($kits->count() > 0) {
                foreach ($kits as $kit) {
                    // Certifica-te se deves exibir $kit->price ou $kit->price_day no HTML
                    $output .= '<div class="col-sm-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body d-flex flex-column justify-content-center text-center">
                                            <h5 class="card-title">' . htmlspecialchars($kit->name, ENT_QUOTES, 'UTF-8') . '</h5>
                                            <p class="card-text">' . number_format($kit->price_day, 2, ',', '.') . '€ / dia</p>
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
            // Correção aqui: Apenas Kit::all(), sem o ->get() à frente
            $kits = Kit::all();
        }

        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.kits.index', ['kits' => $kits]);
        }
        return redirect('/');
    }

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