<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function cities()
    {
        return $this->hasMany('App\City');
    }
}
