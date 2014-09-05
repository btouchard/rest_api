<?php
namespace Library\Utils;

class ArrayUtils {

    public static function keyExists($key, array $array) {
        foreach ($array as $index => $value) {
            if (is_int($index)) self::keyExists($key, $array[$index]);
            else if ($index === $key) return true;
        }
        return false;
    }

}