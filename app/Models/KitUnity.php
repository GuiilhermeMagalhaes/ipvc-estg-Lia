<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitUnity extends Model
{
    protected $table = 'kit_unity';

    protected $fillable = [
        'lia_code',
        'kit_unity_state_id',
        'kit_id',
        'observacoes'
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

    public function itemUnities(): HasMany
    {
        return $this->hasMany(ItemUnity::class, 'kit_unity_id'); 
    }


    public function kitUnityReserves(): HasMany
    {
        return $this->hasMany(KitUnityReserve::class, 'kit_unity_id');
    }

    public function reservaAtiva() 
    {
        $reserva = \Illuminate\Support\Facades\DB::table('kit_unity_reserve')
            ->join('kit_reserve', 'kit_unity_reserve.kit_reserve_id', '=', 'kit_reserve.id')
            ->join('reserves', 'kit_reserve.reserve_id', '=', 'reserves.id')
            ->where('kit_unity_reserve.kit_unity_id', $this->id)
            ->whereIn('reserves.reserve_state_id', [4, 7]) // 4 = Em curso, 7 = Atrasada
            ->select('reserves.id')
            ->first();
        
        return $reserva ? $reserva->id : null;
    }
}
