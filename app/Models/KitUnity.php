<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KitUnity extends Model
{
    protected $table = 'kit_unity';

    protected $fillable = [
        'lia_code',
        'kit_state_id',
        'kit_id'
    ];

    public function kitUnityState(): BelongsTo
    {
        return $this->belongsTo(KitState::class);
    }

    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }
}
