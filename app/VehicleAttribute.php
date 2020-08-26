<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\VehicleAttribute
 *
 * @property int $attribute_id
 * @property int $vehicle_id
 * @property string $text
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleAttribute whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleAttribute whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleAttribute whereVehicleId($value)
 * @mixin \Eloquent
 */
class VehicleAttribute extends Model
{
    public $timestamps = false;
}
