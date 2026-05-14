<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kit extends Model
{

    protected $fillable = [
        'name',
        'description',
        'lia_code',
        'ipvc_ref',
        'price',
        'kit_state_id',
        'image',
        'categoria_id'
    ];
    
    /**
     * Get all of the items for the Kit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the kit_state that owns the Kit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitState(): BelongsTo
    {
        return $this->belongsTo(KitState::class);
    }

    /**
     * Get the kit_state that owns the Kit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitReserve(): BelongsTo
    {
        return $this->belongsTo(KitReserve::class);
    }

        /**
     * Get the itemCategorie that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function itemCategorie(): BelongsTo
    {
        return $this->belongsTo(ItemCategorie::class, 'categoria_id');
    }
}