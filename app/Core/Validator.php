<?php

class Validator
{
    public static function required(string $value, string $label): ?string
    {
        return trim($value) === '' ? "$label is required." : null;
    }

    public static function dateNotPast(string $date, string $label = 'Date'): ?string
    {
        return $date < date('Y-m-d') ? "$label cannot be in the past." : null;
    }
}
