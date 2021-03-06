<?php
namespace App;

use Library\Application;
use Library\Utils\Json;

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
	    	$content = Json::encode($result);
	        $this->response->setContent($content);
	        $this->response->send();
        } catch (\Exception $e) {
            $this->setError($e->getCode(), $e->getMessage());
        }
    }

} 