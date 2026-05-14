<?php

namespace App\Models;;

use Illuminate\Database\Eloquent\Model;

class Disponibilidade extends Model
{
    protected $table = "disponibilidade";

    public $timestamps = false;

    protected $fillable = [
        'data',
        'descricao',
        'data_expiracao',
        'entredatas'
    ];
}
