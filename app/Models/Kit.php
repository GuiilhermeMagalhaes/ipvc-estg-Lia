<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kit extends Model
{

    protected $fillable = [
        'name',
        'description',
        'ipvc_ref',
        'price',
        'price_day',
        'image',
        'categoria_id',
        'quantity', 
        'quantity_disp'
    ];


    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function kitUnits(): HasMany
    {
        return $this->hasMany(kitUnity::class);
    }

}