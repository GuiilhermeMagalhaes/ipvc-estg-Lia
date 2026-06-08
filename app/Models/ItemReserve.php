<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use App\Models\Reserve;

class ItemReserve extends Model
{
    protected $table = 'item_reserve';

    public $timestamps = false;

    protected $fillable = [
        'reserve_id',
        'item_id',
        'item_unity_id',
        'quantity'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id'); 
    }
    
    public function reserve()
    {
        return $this->belongsTo(Reserve::class, 'reserve_id');
    }

    public function itemUnity()
    {
        return $this->belongsTo(ItemUnity::class, 'item_unity_id');
    }
}