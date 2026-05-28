<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kit;
use App\Models\Reserve;

class KitReserve extends Model
{
    protected $table = 'kit_reserve';

    public $timestamps = false;

    protected $fillable = [
        'reserve_id',
        'kit_id'
    ];

    public function kit()
    {
        return $this->belongsTo(Kit::class, 'kit_id'); // Assume que a chave estrangeira se chama 'kit_id'
    }

    public function reserve()
    {
        return $this->belongsTo(Reserve::class, 'reserve_id');
    }
}