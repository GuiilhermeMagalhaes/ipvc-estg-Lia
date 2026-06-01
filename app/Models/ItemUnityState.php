<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemUnityState extends Model
{
    protected $table = 'item_unity_states';

    protected $fillable = [
        'description'
    ];

    public function itemUnities(): HasMany
    {
        return $this->hasMany(ItemUnity::class);
    }
}
