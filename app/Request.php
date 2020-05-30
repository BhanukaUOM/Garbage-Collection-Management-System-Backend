<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'user_id', 'collector_id', 'isApproved', 'locationLat', 'locationLon', 'pickedUp'
    ];

    public function customer()
    {
        return $this->hasMany(Customer::class);
    }

    public function points()
    {
        return $this->belongsToMany(PointType::class);
    }

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
}
