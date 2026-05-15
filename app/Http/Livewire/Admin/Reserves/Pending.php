<?php

namespace App\Http\Livewire\Admin\Reserves;

use App\Models\Reserve;
use Livewire\Component;

class Pending extends Component
{
    public $reserves;

    public function mount(){
        $this->reserves = Reserve::where('reserve_state_id', 1)->get();
    }
    
    public function render()
    {   
        return view('livewire.admin.reserves.pending')
            ->extends('adminlte::page')
            ->section('content');
    }

    public function showReserve($id){
        return redirect()->to('/admin/reserves/'. $id);
    }
}
