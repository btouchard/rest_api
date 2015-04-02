<?php
namespace Mongo;

use Library\Application;

class Api extends Application {

    public function __construct() {
        parent::__construct();
        $this->response->setContentType('application/json');
    }

    public function run() {
        try {
            $router = new Router($this);
            $class = $router->getController();
            $ctrl = new $class($this);
            $result = $ctrl->run();
        } catch (\Exception $e) {
            $this->setError($e->getCode(), $e->getMessage());
        }
    }

} 