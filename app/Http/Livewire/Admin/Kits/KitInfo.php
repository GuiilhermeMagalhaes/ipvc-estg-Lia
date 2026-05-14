<?php

namespace App\Http\Livewire\Admin\Kits;

use App\Models\Kit;
use Livewire\Component;

class KitInfo extends Component
{
    public $kit;

    public function mount($id){
        $this->kit = Kit::find($id);
    }

    public function render()
    {
        return view('livewire.admin.kits.kit-info');
    }
}
