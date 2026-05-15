<?php

namespace App\Http\Livewire\User\Reserve;

use App\Models\CostCenter;
use App\Models\Ciclica;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $costCenters;
    public $start_date;
    public $end_date;
    public $description;
    public $cost_center;
    public $ciclicas;
    
    protected $rules = [
        'start_date' => 'required',
        'end_date' => 'required',
        'description' => 'required',
        'cost_center' => 'required',
        'ciclica_' => 'required',
    ];

    public function mount()
    {
        $this->costCenters = CostCenter::all();
        $this->cost_center = $this->costCenters[0]->id;
        
        $this->ciclicas = Ciclica::all();
        $this->ciclica = $this->ciclicas[0]->id;
    }

    public function render()
    {
        return view('livewire.user.reserve.create');
    }

    public function createReserve()
    {
        $this->validate();

        $reserve = [
            "user_id" => Auth::id(),
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "description" => $this->description,
            "cost_center_id" => $this->cost_center,
            "ciclica_id" => $this->ciclica_,
            "cost" => 0,
            "kits" => []
        ];

        session()->put('reserve', $reserve);

        return redirect()->to('/reserve/info');
    }
}
