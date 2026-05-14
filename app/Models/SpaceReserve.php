<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceReserve extends Model
{
    protected $fillable =  [
        'id',
        'description',
        'start_date',
        'end_date',
        'cost',
        'occupant_id',
        'user_id',
        'space_code'
    ];

    /**
     * The users that belong to the SpaceReserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The lia_spaces that belong to the SpaceReserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function liaSpace(): BelongsToMany
    {
        return $this->belongsToMany(LiaSpace::class);
    }
    
    /**
     * The cost_center that belong to the SpaceReserve
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }


}
