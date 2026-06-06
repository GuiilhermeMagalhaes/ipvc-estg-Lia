<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;

class ItemUnity extends Model
{
    protected $table = 'item_unity';

    protected $fillable = [
        'lia_code',
        'item_id',
        'kit_unity_id',
        'item_unity_state_id', 
        'data_aquisicao'
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function kitUnity(): BelongsTo
    {
        return $this->belongsTo(KitUnity::class, 'kit_unity_id');
    }

    public function itemUnityState(): BelongsTo
    {
        return $this->belongsTo(ItemUnityState::class, 'item_unity_state_id');
    }

    public function getTempoDeVidaAttribute()
    {
        if (!$this->data_aquisicao) {
            return 'Desconhecido';
        }

        // Converte a string da base de dados num objeto Carbon e calcula a diferença para hoje
        $dataAquisicao = Carbon::parse($this->data_aquisicao);
        

        return $this->data_aquisicao->locale('pt')->diffForHumans([
        'parts' => 2, // Ex: "há 3 anos e 2 meses"
        'syntax' => Carbon::DIFF_RELATIVE_TO_NOW
    ]);
    }
}
