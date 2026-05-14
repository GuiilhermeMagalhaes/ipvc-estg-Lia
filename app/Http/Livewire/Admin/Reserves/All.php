<?php

namespace App\Http\Livewire\Admin\Reserves;

use App\Models\Reserve;
use Livewire\Component;

class All extends Component
{
    public $reserves;

    public function mount(){
        $this->reserves = Reserve::all();
    }

    public function render()
    {
        return view('livewire.admin.reserves.all')
                ->extends('adminlte::page')
                ->section('content');
    }

    public function showReserve($id){
        return redirect()->to('/admin/reserves/'. $id);
    }
}
