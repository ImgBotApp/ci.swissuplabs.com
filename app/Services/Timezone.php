<?php

namespace App\Services;

use DateTimeZone;
use Illuminate\Support\Facades\Cookie;

class Timezone
{
    public static function getClientTimezone()
    {
        $timezone = Cookie::get('tz');

        if ($timezone && in_array($timezone, DateTimeZone::listIdentifiers())) {
            return $timezone;
        }

        return config('app.timezone');
    }
}
