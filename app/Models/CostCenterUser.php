<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenterUser extends Model
{
    protected $table = 'cost_center_user';

    public $timestamps = false;

    protected $fillable = [
        'cost_center_id',
        'user_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function centroCustos(): HasMany
    {
        return $this->HasMany(CostCenter::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->HasMany(User::class);
    }
}
