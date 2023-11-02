<?php

if (!function_exists('onlyDigits')) {
    function onlyDigits(string $value): string
    {
        return preg_replace('/\D/','', $value);
    }
}
