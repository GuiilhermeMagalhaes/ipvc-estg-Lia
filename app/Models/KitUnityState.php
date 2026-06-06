<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitUnityState extends Model
{
    protected $table = 'kit_unity_states';

    protected $fillable = [
        'description'
    ];

    public function kitUnities(): HasMany
    {
        return $this->hasMany(KitUnity::class, 'kit_unity_state_id');
    }
}
