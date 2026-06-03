<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
