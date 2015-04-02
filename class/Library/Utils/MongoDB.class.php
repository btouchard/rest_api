<?php
namespace Library\Utils;

use \MongoClient;

class MongoDB {

    private static $instance = null;

    private function __construct() {}

    private function __destruct() {
        if (self::$instance != null)
            self::$instance->close();
    }

    private function __clone() {}

    public static function getInstance() {
        if(!self::$instance) self::$instance = new MongoClient();
        return self::$instance->{MONGO_DB};
    }

}