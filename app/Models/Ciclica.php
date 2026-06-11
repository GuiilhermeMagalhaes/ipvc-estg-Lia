<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ciclica extends Model
{
    protected $table = "ciclica";



   public function reserves(): HasMany // <- Alterado de BelongsToMany para HasMany
    {
        return $this->hasMany(Reserve::class, 'ciclica_id');
    }
}
