<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemUnityReserve extends Model
{
    protected $table = 'item_unity_reserve';

    protected $fillable = [
        'item_reserve_id',
        'item_unity_id',
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function itemReserve(): BelongsTo
    {
        return $this->belongsTo(ItemReserve::class, 'item_reserve_id');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function itemUnity(): BelongsTo
    {
        return $this->belongsTo(ItemUnity::class, 'item_unity_id');
    }
}
