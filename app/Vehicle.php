<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
/**
 * App\Vehicle
 *
 * @property int $id
 * @property string $number
 * @property string|null $name
 * @property string|null $description
 * @property string $url
 * @property float|null $price
 * @property mixed|null $published
 * @property mixed|null $upped
 * @property int $category_id
 * @property int $platform_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle wherePlatformId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereUpped($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Vehicle whereUrl($value)
 * @mixin \Eloquent
 */
class Vehicle extends Model
{
//    use QueryCacheable;
//    protected $cacheFor = 3600;
    protected $guarded = [
        '_token'
    ];
    public $timestamps = true;
    protected $casts = [
        'published' => 'datetime:d.m.Y',
        'upped'     => 'datetime:d.m.Y'
    ];

    public function insertAndSendToTelegramm(array $data){

    }
}
