<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ItemCategorie extends Model
{
    protected $fillable = [
        'description',
        'image'
    ];
    /**
     * Get all of the itens for the ItemCategorie
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function itens(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
