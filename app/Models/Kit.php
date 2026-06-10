<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kit extends Model
{
    protected $table = 'kits';

    protected $fillable = [
        'name',
        'description',
        'ipvc_ref',
        'price',
        'price_day',
        'image',
        'quantity', 
    ];


    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function kitUnities(): HasMany 
    {
        return $this->hasMany(KitUnity::class, 'kit_id');
    }

    public function kitReserves(): HasMany
    {
        return $this->hasMany(KitReserve::class, 'kit_id');
    }

}