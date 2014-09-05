<?php
namespace App;

use Library\Component;
use App\FileSystem;

class RouterException extends \Exception {}

class Router extends Component {

    public function getController() {
    	if (0 === strpos($this->app->request()->request(), FileSystem::MEDIAS_PATH)) return '\\App\\FileSystem';
        return '\\App\\Table';
    }
}