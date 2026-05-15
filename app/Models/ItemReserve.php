<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;




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
}
