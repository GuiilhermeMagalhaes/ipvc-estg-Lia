<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Item;
use App\Models\Reserve;




class ItemReserve extends Model
{
    protected $table = 'item_reserve';

    public $timestamps = false;

    protected $fillable = [
        'reserve_id',
        'item_id'
    ];

    /**
     * Get all of the items for the Reserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reserves(): HasMany
    {
        return $this->HasMany(Reserve::class);
    }
    
    /**
     * Get all of the items for the Reserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function itens(): HasMany
    {
        return $this->HasMany(Item::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id'); // Assume que a tua chave estrangeira se chama 'item_id'
    }

    
}
