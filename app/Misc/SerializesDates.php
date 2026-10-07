<?php

namespace App\Misc;

/**
 * Serialize model dates in toArray() / JSON as "Y-m-d H:i:s" (Laravel 5.5 format),
 * instead of ISO-8601 used since Laravel 7. Keeps API responses and broadcasts unchanged.
 */
trait SerializesDates
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->getDateFormat());
    }
}
