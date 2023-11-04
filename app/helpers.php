<?php

if (!function_exists('onlyDigits')) {
    function onlyDigits(string $value): string
    {
        return preg_replace('/\D/','', $value);
    }
}

if (!function_exists('json_decode_file')) {
    function json_decode_file(string $filePathName)
    {
        return json_decode(file_get_contents($filePathName));
    }
}
