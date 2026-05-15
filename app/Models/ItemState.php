<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemState extends Model
{
    /**
     * Get all of the itens for the ItemState
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function itens(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
