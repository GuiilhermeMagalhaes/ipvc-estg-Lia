<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceItem extends Model
{
    protected $fillable =  [
        'lia_space_id',
        'description',
        'lia_code',
    ];
    
    /**
     * Get the espaco_lia that owns the SpaceItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function espacoLia(): BelongsTo
    {
        return $this->belongsTo(LiaSpace::class);
    }
}
