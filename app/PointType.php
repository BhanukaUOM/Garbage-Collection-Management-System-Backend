<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointType extends Model
{
    protected $fillable = [
        'name', 'point', 'description'
    ];
}
