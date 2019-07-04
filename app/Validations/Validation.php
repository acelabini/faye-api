<?php

namespace App\Validations;

abstract class Validation implements ValidationContract
{
    public static function getRules()
    {
        return static::$rules;
    }
}