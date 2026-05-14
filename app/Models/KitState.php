<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitState extends Model
{
    /**
     * Get all of the kits for the KitState
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kits(): HasMany
    {
        return $this->hasMany(Kit::class);
    }
}
