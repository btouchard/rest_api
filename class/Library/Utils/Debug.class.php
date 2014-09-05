<?php
namespace Library\Utils;

class Debug {
    public static function log($msg) {
        echo '<pre>';
        self::writeLog($msg);
        if (func_num_args() > 1) {
            for ($i=1; $i<func_num_args(); $i++) self::writeLog(func_get_arg($i));
        }
        echo '</pre>';
    }
    private static function writeLog($msg) {
        if (gettype($msg) == 'object' && method_exists($msg, '__toString')) echo $msg . "\n";
        else if (gettype($msg) == 'string') echo $msg . "\n";
        else var_dump($msg);
    }
}