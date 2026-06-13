<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ciclica extends Model
{
    protected $table = "ciclica";



   public function reserves(): HasMany 
    {
        return $this->hasMany(Reserve::class, 'ciclica_id');
    }
}
