<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserve;
use App\Models\Kit;
use App\Models\KitReserve;
use App\Models\Item;
use App\Models\ItemReserve;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF;

class ReserveController extends Controller
{
    public function all()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.all', ['reserves' => Reserve::all()]);
        }
        return redirect('/');
    }

    public function pending()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.pending', ['reserves' => Reserve::where('reserve_state_id', 1)->get()]);
        }
        return redirect('/');
    }

    public function delayed()
    {
        $reservas = Reserve::all();

        foreach ($reservas as $reserve) {
            if ($reserve->reserve_state_id == 7) {
                $todaydate = date('Y-m-d');
                if ($todaydate > $reserve->end_date) {
                    $reserve->reserve_state_id = 4;
                }
                $reserve->save();
            }
        }
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.delayed', ['reserves' => Reserve::where('reserve_state_id', '4')->orWhere('reserve_state_id', '9')->get()]);
        }
        return redirect('/');
    }

    public function ongoing()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.ongoing', ['reserves' => Reserve::where('reserve_state_id', '7')
                                                ->orWhere('reserve_state_id', '8')->get()]);
        }
        return redirect('/');
        
    }

    public function unauthorized()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.unauthorized', ['reserves' => Reserve::where('reserve_state_id', '3')->get()]);
        }
        return redirect('/');
        
    }

    public function completed()
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.completed', ['reserves' => Reserve::where('reserve_state_id', '5')->orWhere('reserve_state_id', '6')->get()]);
        }
        return redirect('/');
    }

    public function show($id)
    {
        if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2) {
            return view('admin.reserves.show', [
                'reserve' => Reserve::find($id),
                'reserve_kits' => KitReserve::where('reserve_id', $id)->get(),
                'kits' => Kit::all(),
                'reserve_itens' => ItemReserve::where('reserve_id', $id)->get(),
                'itens' => Item::all()
            ]);
        }
        return redirect('/');
        
    }

    public function PDFDownload($id)
    {
        $reserve = Reserve::find($id);
        $reserve_kits = KitReserve::where('reserve_id', $id)->get();
        $kits = Kit::all();
        $reserve_itens = ItemReserve::where('reserve_id', $id)->get();
        $itens = Item::all();
        $pdf = PDF::loadview('admin.reserves.PDF', compact('reserve', 'reserve_kits', 'kits', 'reserve_itens', 'itens'));

        return $pdf->download('Requisicao.pdf');
    }

    public function autorize($id)
    {
        $reserve = Reserve::find($id);
        $reserve->reserve_state_id = 2;
        $reserve->save();

        return back();
    }

    public function decline($id)
    {
        $reserve = Reserve::find($id);
        $reserve->reserve_state_id = 3;
        $reserve->save();

        return back();
    }

    public function finalize($id)
    {
        $reserve = Reserve::find($id);
        $todaydate = date('Y-m-d');
        if($todaydate <= $reserve->end_date){
            $reserve->reserve_state_id = 5;
        }else{
            $reserve->reserve_state_id = 6;
        }
        $reserve->save();
        return back();
    }

    public function deliver($id)
    {
        $reserve = Reserve::find($id);
        $reserve->reserve_state_id = 7;
        $reserve->delivery_date = Carbon::now(); // Define a data de entrega como a data atual
        $reserve->save();
        return back();
    }

    public function receive($id)
    {
        $reserve = Reserve::find($id);
        $reserve->return_date = Carbon::now(); // Define a data de retorno como a data atual
        $todaydate = date('Y-m-d');

        if($todaydate <= $reserve->end_date){
            $reserve->reserve_state_id = 8;
        }else{
            $reserve->reserve_state_id = 9;
        }
        $reserve->save();
        return back();
    }
}
