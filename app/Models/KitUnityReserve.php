<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitUnityReserve extends Model
{
    
    protected $table = 'kit_unity_reserve';

   
    protected $fillable = [
        'kit_reserve_id',
        'kit_unity_id'
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitReserve(): BelongsTo
    {
        return $this->belongsTo(KitReserve::class, 'kit_reserve_id');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitUnity(): BelongsTo
    {
        return $this->belongsTo(KitUnity::class, 'kit_unity_id');
    }
}
