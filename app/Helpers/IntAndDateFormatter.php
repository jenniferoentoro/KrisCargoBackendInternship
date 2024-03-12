<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class IntAndDateFormatter
{
    public static function format($data)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {

                if (is_array($value)) {
                    $data[$key] = self::format($value);
                } elseif (is_object($value)) {
                    $data[$key] = self::formatObject($value);
                } elseif (self::isDateAttribute($value)) {
                    $data[$key] = Carbon::parse($value)->format('d-m-Y');
                } elseif (is_int($value)) {

                    $data[$key] = number_format($value, 0, ',', '.');
                } else if (is_double($value)) {
                    // Switch period to comma
                    $data[$key] = number_format($value, 2, ',', '.'); // Example: 2 decimal places
                } else if (is_float($value)) {
                    // Switch period to comma
                    $data[$key] = number_format($value, 2, ',', '.'); // Example: 2 decimal places
                }
            }
        }

        return $data;
    }

    public static function formatObject($object)
    {
        return self::format($object->attributesToArray());
    }

    public static function isDateAttribute($value)
    {
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        return preg_match($pattern, $value) === 1;
    }
}
