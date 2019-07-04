<?php

namespace App\Utils\Enumerators;

use ReflectionClass;

trait Enumerates
{
    private function __construct(){}

    /**
     * Check if the given value exist
     * @param  string  $value
     * @return boolean
     */
    public static function isValid($value)
    {
        return in_array($value, self::getConstants());
    }

    /**
     * Check if given constant in declared and valid
     * @param  string  $contant name
     * @return boolean
     */
    public static function isValidKey($constant)
    {
        return array_key_exists($constant, self::getConstants());
    }

    /**
     * Get list of declared constants
     *
     * @return array constants
     */
    public static function getConstants() : array
    {
        return (new ReflectionClass(self::class))->getConstants();
    }
}