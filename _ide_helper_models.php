<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Attribute
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attribute query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Attribute whereName($value)
 * @mixin \Eloquent
 */
	class Attribute extends \Eloquent {}
}

namespace App{
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
 */
	class Category extends \Eloquent {}
}

namespace App{
/**
 * App\Option
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Option whereName($value)
 * @mixin \Eloquent
 */
	class Option extends \Eloquent {}
}

namespace App{
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
	class Platform extends \Eloquent {}
}

namespace App{
/**
 * App\TelegramUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser query()
 */
	class TelegramUser extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

namespace App{
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
	class Vehicle extends \Eloquent {}
}

namespace App{
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
	class VehicleAttribute extends \Eloquent {}
}

namespace App{
/**
 * App\VehicleOption
 *
 * @property int $option_id
 * @property int $vehicle_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleOption whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\VehicleOption whereVehicleId($value)
 * @mixin \Eloquent
 */
	class VehicleOption extends \Eloquent {}
}

