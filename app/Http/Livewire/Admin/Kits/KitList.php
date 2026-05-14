<?php

namespace App\Http\Livewire\Admin\Kits;

use App\Models\Kit;
use Livewire\Component;

class KitList extends Component
{
    public $search = '';
    public $kits;
    
    public function render()
    {
        if(empty($this->search)){
            $this->kits = Kit::all();
        } else {
            $this->kits = Kit::where('description', 'LIKE', '%'.$this->search.'%')
                                ->orWhere('lia_code', 'LIKE', '%'.$this->search.'%')->get();
        }

        return view('livewire.admin.kits.kit-list');
    }

    public function showKit($id){
        return redirect()->to('/admin/kits/'. $id);
    }
}
