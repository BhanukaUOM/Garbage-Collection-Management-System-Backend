<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collector extends Model
{
    protected $fillable = [
        'vehicleNo'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function points(){
        return $this->hasMany(Point::class);
    }

    public function requests(){
        return $this->hasMany(Request::class);
    }
}
