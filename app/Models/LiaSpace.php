<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiaSpace extends Model
{

    protected $fillable = [
        'id',
        'description',
        'pc',
        'teclado',
        'rato',
        'lia_code',
        'cost',
        'space_code'
    ];

    /**
     * Get all of the itens for the LiaSpace
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function itens(): HasMany
    {
        return $this->hasMany(SpaceItem::class);
    }

    /**
     * The spaceReserve that belong to the LiaSpace
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function spaceReserve(): BelongsToMany
    {
        return $this->belongsToMany(SpaceReserve::class);
    }

    /**
     * Get all of the items for the Kit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
