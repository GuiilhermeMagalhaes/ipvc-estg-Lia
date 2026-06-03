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

    public $timestamps = false;

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
        'quantity_disp',  
    ];

    

   
    public function itemUnities(): hasMany
    {
        return $this->hasMany(ItemUnity::class, 'item_id'); 
    }

    
    public function itemCategorie(): BelongsTo
    {
        return $this->belongsTo(ItemCategorie::class, 'categoria_id');
    }

    
     public function getTempoDeVidaAttribute()
{
    if (!$this->data_aquisicao) {
        return 'Data de aquisição desconhecida';
    }
   
}
}