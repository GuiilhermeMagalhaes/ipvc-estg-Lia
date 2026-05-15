<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategorie;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        if (Auth::user()->user_type_id == 1) {
            return view('admin.categories.index', ['categories' => ItemCategorie::all()]);
        }
        return redirect('/');
    }

    public function create()
    {
        if (Auth::user()->user_type_id == 1) {
            return view('admin.categories.create');
        }
        return redirect('/');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);

        if ($request->image != null) {
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/categories', $imageName, 'public');
        } else {
            $path = "images/categories/sc.jpg";
        }

        ItemCategorie::create([
            'description' => $request->description,
            'image' => $path
        ]);

        return redirect()->to('/admin/categories');
    }

    public function edit($id)
    {
        if (Auth::user()->user_type_id == 1) {
            return view('admin.categories.edit', ['categoria' => ItemCategorie::find($id)]);
        }
        return redirect('/');
    }

    public function update(Request $request, $id)
    {
        $categoria = ItemCategorie::find($id);


        $request->validate(
            [
                'description' => 'required'
            ],
            [
                'description.required' => 'A categoria deve ter um nome'
            ]
        );

        if ($request->image != null) {
            $imagePath = $request->file('image');
            $imageName = time() . '.' . $imagePath->getClientOriginalExtension();
            $path = $request->file('image')->storeAs('images/categories', $imageName, 'public');
        } else {
            $path = $categoria->image;
        }

        $categoria->update([
            'description' => $request->description,
            'image' => $path
        ]);

        $categoria->save();

        return redirect()->to('/admin/categories');
    }

    public function destroy($id)
    {
        $itensCategoria = Item::where('categoria_id', $id)->get();
        if ($itensCategoria->isEmpty()) {
            $categoria = ItemCategorie::find($id);
            $categoria->delete();
        } else {
            return redirect()->to('/admin/categories')->with('toast_error', 'Existem itens com esta categoria!');
        }
        return redirect()->to('/admin/categories')->with('toast_success', 'Categoria Apagada');
    }
}
