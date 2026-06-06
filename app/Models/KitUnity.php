<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitUnity extends Model
{
    protected $table = 'kit_unity';

    protected $fillable = [
        'lia_code',
        'kit_unity_state_id',
        'kit_id'
    ];


    public function kitUnityState(): BelongsTo
    {
        return $this->belongsTo(KitUnityState::class, 'kit_unity_state_id');
    }

    // CORREÇÃO: Forçamos o uso da coluna 'kit_id' para garantir consistência
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class, 'kit_id');
    }
}
