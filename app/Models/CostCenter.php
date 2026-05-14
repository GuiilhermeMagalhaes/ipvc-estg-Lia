<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostCenter extends Model
{
    protected $fillable = [
        'name',
        'total_cost',
        'total_debt'
    ];

    /**
     * Get all of the reserves for the CostCenter
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function reserves(): HasMany
    {
        return $this->hasMany(Reserve::class);
    }

    /**
     * Get all of the space_reserves for the CostCenter
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function spaceReserve(): HasMany
    {
        return $this->hasMany(SpaceReserve::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function costCenterUser(): BelongsTo
    {
        return $this->belongsTo(CostCenterUser::class);
    }
}
