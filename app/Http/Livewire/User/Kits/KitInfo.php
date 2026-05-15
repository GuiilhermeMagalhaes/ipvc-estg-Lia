<?php

namespace App\Http\Livewire\User\Kits;

use App\Models\Kit;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class KitInfo extends Component
{
    //use LivewireAlert;

    public $kit;
    public $error = null;

    protected $listeners = [
        'createReserve'
    ];
    
    public function mount($id){
        $this->kit = Kit::find($id);
    }

    public function render()
    {
        return view('livewire.user.kits.kit-info');
    }

    public function addKit(){
        if(session()->missing('reserve')){
            $this->alert('warning', 'Tem de criar uma reserva para poder adicionar kits!',[
                'position' => 'center',
                'timer' => null,
                'toast' => false,
                'showConfirmButton' => true,
                'confirmButtonText' => 'Criar reserva',
                'onConfirmed' => 'createReserve'
            ]);
            return;
        }
        
        if(empty(session()->get('reserve.kits'))){
            session()->push('reserve.kits', $this->kit);
            session()->increment('reserve.cost', $this->kit->price);
            $this->alert('success', 'Kit adicionado!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true,
                ]);
        }else{
            if(in_array($this->kit, session()->get('reserve.kits'))){
                $this->alert('warning', 'Kit já adicionado!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                    ]);
            }
            else{
                session()->push('reserve.kits', $this->kit);
                session()->increment('reserve.cost', $this->kit->price);
                $this->alert('success', 'Kit adicionado!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                    ]);
            }
        }
        return;
    }

    public function createReserve(){
        return redirect()->to('/reserve');
    }
}
