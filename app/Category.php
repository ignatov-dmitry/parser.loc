<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Category
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property int $parent_id
 * @property int $platform_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category wherePlatformId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereUrl($value)
 * @mixin \Eloquent
 * @property string|null $release_start
 * @property string|null $release_end
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereReleaseEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereReleaseStart($value)
 */
class Category extends Model
{
    protected $guarded = [
        '_token',
        'sub_categories'
    ];

    public $timestamps = false;
}
