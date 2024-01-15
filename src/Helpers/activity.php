<?php

use Kopaing\SimpleLog\Helpers\ActivityLogger;

if (!function_exists('activity')) {
    function activity($logName = null)
    {
        return new ActivityLogger($logName);
    }

}
