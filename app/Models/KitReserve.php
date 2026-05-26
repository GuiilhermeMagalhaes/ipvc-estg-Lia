<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Item;
use App\Models\Reserve;




class KitReserve extends Model
{
    protected $table = 'kit_reserve';

    public $timestamps = false;

    protected $fillable = [
        'reserve_id',
        'kit_id'
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
    public function kits(): HasMany
    {
        return $this->HasMany(Kit::class);
    }

    public function kit()
    {
        return $this->belongsTo(Kit::class, 'kit_id'); // Assume que a chave estrangeira se chama 'kit_id'
    }
}
