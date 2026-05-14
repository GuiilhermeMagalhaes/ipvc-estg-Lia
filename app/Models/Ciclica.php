<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ciclica extends Model
{
    protected $table = "ciclica";
    /**
     * Get the reserve that owns the Ciclica
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reserves(): BelongsToMany
    {
        return $this->belongsToMany(Reserve::class);
    }
}
