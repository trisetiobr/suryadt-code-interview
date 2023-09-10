<?php

namespace App\Helpers;

use DateTimeZone;

class LocationHelper
{
    public static function locations()
    {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }
}
