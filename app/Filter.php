<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Filter
 *
 * @property int $id
 * @property int $country_id
 * @property int $region_id
 * @property int $city_id
 * @property int $brand
 * @property string $chat_id
 * @method static \Illuminate\Database\Eloquent\Builder|Filter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Filter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Filter query()
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereRegionId($value)
 * @mixin \Eloquent
 */
class Filter extends Model
{
    public $timestamps = false;
}
