<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{

    protected $fillable = [
        'id',
        'title',
        'note',
        'date',
        'start',
        'end',
        'state',
        'user',
        'created_by',
        'updated_by',
    ];

}
