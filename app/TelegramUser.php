<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TelegramUser
 *
 * @property int $id
 * @property string $chat_id
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TelegramUser whereId($value)
 * @mixin \Eloquent
 */
class TelegramUser extends Model
{
    public $timestamps = false;
}
