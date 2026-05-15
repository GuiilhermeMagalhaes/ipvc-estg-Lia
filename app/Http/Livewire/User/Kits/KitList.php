<?php

namespace App\Http\Livewire\User\Kits;

use App\Models\Kit;
use Livewire\Component;

class KitList extends Component
{
    public $search = '';
    public $kits;
    public $category_id;

    public function mount($id){
        $this->category_id = $id;
    }

    public function render()
    {
        if($this->hasSession()){
            $this->kits = $this->filteredKits();
        }
        else{
            if(empty($this->search)){
                $this->kits = Kit::where('kit_category_id', $this->category_id)->get();
            } else {
                $this->kits = Kit::where('description', 'LIKE', '%'.$this->search.'%')
                                    ->where('kit_category_id', $this->category_id)->get();
            }
        }

        return view('livewire.user.kits.kit-list', ['kits' => $this->kits]);
    }

    private function hasSession(){
        if(session()->has('reserve')){
            return true;
        }
        
        return false;
    }

    private function filteredKits(){
        $kits = Kit::where('kit_category_id', $this->category_id)->doesntHave('reserves')
                ->orWhere('kit_category_id', $this->category_id)->whereHas('reserves', function($query){
                    $query->where(function($q){
                                $q-> whereNotBetween('start_date', [session()->get('reserve.start_date'), session()->get('reserve.end_date')])
                                ->whereNotBetween('end_date', [session()->get('reserve.start_date'), session()->get('reserve.end_date')]);
                            })
                            ->where(function($q){
                                    $q->where('start_date', '>' , session()->get('reserve.end_date'))
                                    ->orWhere('end_date', '<', session()->get('reserve.start_date'));
                            });
                    })
                ->get();

        return $kits;
    }
}
