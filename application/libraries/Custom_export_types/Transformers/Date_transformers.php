<?php

final class Date_transformers
{
    static function year_from_date(string $input)
    {
        $input = trim($input);
        $date = date_create($input);

        if (!$date) {
            throw new InvalidArgumentException("Invalid date input: {$input}. Expected format: 'YYYY', 'YYYY-MM', or 'YYYY-MM-DD'.");
        }

        return $date->format('Y');
    }

    static function datacite_date($input, $parameters)
    {
        return '';
    }

    static function updated_dates($input, $parameters)
    {
        return '';
    }
}
