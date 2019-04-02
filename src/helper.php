<?php
/**
 * Created by PhpStorm.
 * User: dawood.ikhlaq
 * Date: 02/04/2019
 * Time: 15:29
 */

function rootDirectory()
{
    return dirname(dirname(__FILE__));
}

/**
 * @param $key
 * @param null $default
 * @return array|false|null|string
 */
function env($key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        $value = $default;
    }
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return;
    }
    return $value;
}