<?php


namespace Engine\System;


use php\util\Regex;

class RegexPatterns
{
    const
        FLOAT = "^-?(\d|[1-9]+\d*|\.\d+|0\.\d+|[1-9]+\d*\.\d+)$",
        INT = "^[0-9][0-9]*$";

    public static function isFloat(string $value): bool{
        return Regex::match(RegexPatterns::FLOAT, $value);
    }

    public static function isInt(string $value): bool{
        return Regex::match(RegexPatterns::INT, $value);
    }
}