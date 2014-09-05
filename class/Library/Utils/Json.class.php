<?php
namespace Library\Utils;

class Json {
    public static function encode($data) {
        if( is_array($data) && empty($data)) return '[]';
        else if( is_array($data) && is_int(key($data)) ) {
            return '[' . self::implode($data, ',', function( $value, $key ) {
                return self::encode($value);
            }) . ']';
        }
        else if( is_array($data) ) {
            return '{' . self::implode($data, ',', function( $value, $key ) {
                return self::encode((string)$key) . ':' . self::encode($value);
            }) . '}';
        }
        else if( is_object($data) ) {
            return $data instanceof ISerializable
                ? self::encode($data->asSerializable())
                : self::encode(get_object_vars($data));
        }
        else {
            return json_encode($data);
        }
    }

    public static function implode(array $arr, $delimiter=',', $callback=null ) {
        $callback   = $callback ?: function($value,$key) { return $value; };
        $result     = '';
        foreach( $arr AS $key => $value ) {
            $result .= (empty($result) ? '' : $delimiter) . $callback($value, $key);
        }
        return $result;
    }
}