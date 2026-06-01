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
        'price_day',
        'categoria_id' ,
        'item_state_id',
        'image',
        'lia_code',
        'observation',
        'acessorio',
        'data_aquisicao'
    ];

    protected $casts = [
        'data_aquisicao' => 'date',
    ];
    /**
     * Get the kit that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the reserve that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reserves(): BelongsToMany
    {
        return $this->belongsToMany(Reserve::class, 'id');
    }

    /**
     * Get the itemCategorie that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function itemCategorie(): BelongsTo
    {
        return $this->belongsTo(ItemCategorie::class, 'categoria_id');
    }

    /**
     * Get the item_state that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function itemState(): BelongsTo
    {
        return $this->belongsTo(ItemState::class);
    }

    public function getTempoDeVidaAttribute()
{
    if (!$this->data_aquisicao) {
        return 'Data de aquisição desconhecida';
    }

    return $this->data_aquisicao->locale('pt_PT')->diffForHumans(null, true);
}
}