<?php

namespace App\Http\Livewire\Admin\Kits;

use App\Models\KitCategory;
use Livewire\Component;

class KitCreate extends Component
{
    public $categories;
    public $description;
    public $lia_code;
    public $ipvc_ref;
    public $price;
    public $kits = [];
    public $items = [];

    protected $rules = [

    ];

    public function mount(){
        $this->categories = KitCategory::all();
    }

    public function render()
    {
        return view('livewire.admin.kits.kit-create');
    }

    public function addKit(){
        $this->kits[] = [''];
    }

    public function addItem(){
        $this->items[] = ['description' => ''];
    }

    public function createKit(){

    }
}
