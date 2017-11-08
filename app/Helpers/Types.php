<?php

use Provisioning\Exceptions\InvalidMacAddressException;

/**
 * Take a boolean or an array of mixed data and converts
 * each boolean to its equivalent in string
 * true becomes "true"
 * false becomes "false"
 */
function booleanToString($boolean)
{
    if (is_array($boolean)) {
        return array_map(function($item) {
            return booleanToString($item);
        }, $boolean);
    }

    if ($boolean === true)
        return 'true';
    elseif ($boolean === false)
        return 'false';
    else
        return $boolean;
}

function convertNull($value)
{
    if (is_array($value)) {
        return array_map(function($item) {
            return convertNull($item);
        }, $value);
    }

    if ($value === null)
        return '@NULL';
    else
        return $value;
}

function associativeArrayToString($array)
{
    if (!is_array($array))
        return $array;

    $ret = [];
    foreach ($array as $key => $value) {
        if (is_array($value))
            $value = '{' . associativeArrayToString($value) . '}';
        $ret[] = $key . '=' . $value;
    }
    return implode(', ', $ret);
}

function macAddress($string)
{
    if (strlen($string) != 12)
        throw new InvalidMacAddressException('Invalid MAC address ' . $string);
    return implode(':', str_split($string, 2));
}