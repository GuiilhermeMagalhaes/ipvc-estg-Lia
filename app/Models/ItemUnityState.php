<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
