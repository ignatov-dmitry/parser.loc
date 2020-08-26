<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Platform
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Platform newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Platform newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Platform query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Platform whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Platform whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Platform whereUrl($value)
 * @mixin \Eloquent
 */
class Platform extends Model
{
    protected $guarded = [
        '_token'
    ];
}
