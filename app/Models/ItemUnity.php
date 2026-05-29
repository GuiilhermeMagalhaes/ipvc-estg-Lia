<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnity extends Model
{
    protected $table = 'item_unity';

    protected $fillable = [
        'lia_code',
        'item_id',
        'kit_unity_id',
        'item_unity_state_id'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function kitUnity(): BelongsTo
    {
        return $this->belongsTo(KitUnity::class);
    }

    public function itemUnityState(): BelongsTo
    {
        return $this->belongsTo(ItemUnityState::class, 'item_unity_state_id');
    }
}
