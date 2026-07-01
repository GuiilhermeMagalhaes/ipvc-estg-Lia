<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reserve extends Model
{
    protected $fillable = [
        'description',
        'cost_center_id',
        'ciclica_id',
        'user_id',
        'start_date',
        'end_date',
        'cost',
        'reserve_state_id',
        'estimated_cost',
        'delivery_date',
        'return_date',
        'is_paid'
    ];

    /**
     * Get the cost_center that owns the Reserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }



    /**
     * Get all of the items for the Reserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function itemReserves(): HasMany
    {
        return $this->hasMany(ItemReserve::class, 'reserve_id');
    }

    
   public function kitReserves(): HasMany
    {
        return $this->hasMany(KitReserve::class, 'reserve_id');
    }

    /**
     * Get the reserveState that owns the Reserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reserveState(): BelongsTo
    {
        return $this->belongsTo(ReserveState::class);
    }

    /**
     * Get the user that owns the Reserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ciclica that owns the Reserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ciclica(): BelongsTo
    {
        return $this->belongsTo(Ciclica::class);
    }
}
