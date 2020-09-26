<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    public $timestamps = false;


    public function regions()
    {
        return $this->hasMany('App\Region');
    }
}
