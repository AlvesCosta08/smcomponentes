<?php

/*
|--------------------------------------------------------------------------
| Global Helper Functions
|--------------------------------------------------------------------------
|
| You can define custom helper functions here that will be available
| globally throughout your application.
|
*/

if (! function_exists('format_currency')) {
    /**
     * Format a value as currency (BRL)
     */
    function format_currency($value, $symbol = true)
    {
        $formatted = number_format($value, 2, ',', '.');
        return $symbol ? "R$ {$formatted}" : $formatted;
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date to Brazilian format
     */
    function format_date($date, $format = 'd/m/Y')
    {
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }
        
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (! function_exists('only_numbers')) {
    /**
     * Remove all non-numeric characters from a string
     */
    function only_numbers($string)
    {
        return preg_replace('/[^0-9]/', '', (string) $string);
    }
}
