<?php

namespace App\Http\Livewire\Admin\Reserves;

use App\Models\Reserve;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;

use PDF;

class Show extends Component
{
    //use LivewireAlert;

    public $reserve;

    protected $listeners = ['refresh' => '$refresh'];
    
    public function mount($id){
        $this->reserve = Reserve::find($id);
    }

    public function render()
    {
        return view('livewire.admin.reserves.show')
            ->extends('adminlte::page')
            ->section('content');
    }

    public function accept(){
        $this->reserve->reserve_state_id = 2;
        $this->reserve->save();

        $this->alert('success', 'Reserva foi aceite!', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true
        ]);

        $this->emit('refresh');
    }

    public function decline(){
        $this->reserve->reserve_state_id = 3;
        $this->reserve->save();

        $this->alert('success', 'Reserva foi recusada!', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true
        ]);

        $this->emit('refresh');
    }

    public function downloadPDF(){
        $reserve = $this->reserve;
        $pdf = PDF::loadview('pdf.PDF', compact('reserve'))->output();
/*
        return response()->streamDownload(
            fn () => print($pdf),
            'Requisicao.pdf');*/
    }
}
