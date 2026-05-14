<?php

namespace App\Http\Livewire\User\Reserve;

use App\Models\CostCenter;
use App\Models\Kit;
use App\Models\Reserve;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class Info extends Component
{
    //use LivewireAlert;

    protected $listeners = [
        'confirmCancel',
        'endReserve'
    ];

    public function render()
    {
        return view('livewire.user.reserve.info');
    }

    public function cancelReserve(){
        $this->alert('question', 'Cancelar reserva de items?', [
            'position' => 'center',
            'timer' => null,
            'toast' => false,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Cancelar reserva',
            'onConfirmed' => 'confirmCancel'
        ]);
    }

    public function confirmCancel(){
        session()->pull('reserve');

        return redirect()->to('/reserve');
    }

    public function confirmReserve(){
        if(empty(session()->get('reserve.kits'))){
            $this->alert('warning', 'Adicione kits para concluir a reserva!', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false
            ]);
            return;
        }

        $this->alert('question', 'Concluir reserva de items?', [
            'position' => 'center',
            'timer' => null,
            'toast' => false,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Concluir reserva',
            'onConfirmed' => 'endReserve'
        ]);
    }

    public function endReserve(){
        
        $reserve = Reserve::create([
            'description' => session()->get('reserve.description'),
            'cost_center_id' => session()->get('reserve.cost_center_id'),
            'user_id' => session()->get('reserve.user_id'),
            'start_date' => session()->get('reserve.start_date'),
            'end_date' => session()->get('reserve.end_date'),
            'cost' => session()->get('reserve.cost'),
            'reserve_state_id' => 1
        ]);

        $costCenter = CostCenter::find(session()->get('reserve.cost_center_id'));
        $costCenter->total_cost += session()->get('reserve.cost');
        $costCenter->save();

        foreach(session()->get('reserve.kits') as $kit){
            $reserve->kits()->attach($kit->id);
        }

        session()->forget('reserve');

        return redirect()->to('/');
    }

    public function removeKit($id){
        $kits = session()->pull('reserve.kits');
        foreach ($kits as $kit) {
            if($kit->id == $id){
                array_splice($kits, $kit->id);
                $this->alert('success', 'Kit removido!', [
                    'position' => 'top-end',
                    'timer' => 3000,
                    'toast' => true,
                    ]);
            }
            else {
                session()->push('reserve.kits', $kit);
            }
        }
    }
}
