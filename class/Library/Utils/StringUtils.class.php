<?php
namespace Library\Utils;

class StringUtils {

    static function toUrl($str) {
        $clean = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $str);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace('/[\/_|+ -]+/', '-', $clean);
        return trim($clean, '-');
    }

    static function startWith($start, $str) {
        return substr($str, 0, strlen($start)) == $start;
    }

    static function endWith($end, $str) {
        return substr($str, strlen($str)-strlen($end)) == $end;
    }

    static function getClassName($class) {
        $arr = explode('\\', $class);
        return array_pop($arr);
    }
}