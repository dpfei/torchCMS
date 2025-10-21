<?php

namespace App\Traits;

use DateTimeInterface;

trait HasDateTimeFormatterTrait
{
    protected function serializeDate(DateTimeInterface $dateTime)
    {
        return $dateTime->format('Y-m-d H:i:s');
    }
}
