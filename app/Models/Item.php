<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    protected $table = "item";


    protected $fillable = [
        'nome',
        'ipvc_ref',
        'model',
        'serial_number',
        'preco',
        'categoria_id',
        'image',
        'observation',
        'data_aquisicao',
        'acessorio', 
        'price_day',
        'item_state_id',
        'quantity',
        'data_aquisicao', 
    ];

    
protected $casts = [
        'data_aquisicao' => 'date',
    ];
   
    public function itemUnities(): hasMany
    {
        return $this->hasMany(ItemUnity::class, 'item_id'); 
    }

    
    public function itemCategorie(): BelongsTo
    {
        return $this->belongsTo(ItemCategorie::class, 'categoria_id');
    }

    
 /*    public function getTempoDeVidaAttribute()
{
    if (!$this->data_aquisicao) {
        return 'Data de aquisição desconhecida';
    }
   
}
*/

public function getTempoDeVidaAttribute()
    {
        if (!$this->data_aquisicao) {
            return 'Data de aquisição desconhecida';
        }

        // Converte a string da base de dados num objeto Carbon e calcula a diferença para hoje
        $dataAquisicao = Carbon::parse($this->data_aquisicao);
        
        return $dataAquisicao->diffForHumans([
            'parts' => 2, // Ex: "há 3 anos e 2 meses"
            'syntax' => Carbon::DIFF_RELATIVE_TO_NOW
        ]);
    }
}