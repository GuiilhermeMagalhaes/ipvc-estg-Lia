<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReserveState extends Model
{
    /**
     * Get all of the reserves for the ReserveState
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reserve(): HasMany
    {
        return $this->hasMany(Reserve::class);
    }
}
