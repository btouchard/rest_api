<?php
namespace App;

use Library\Component;
use Library\Utils\Json;
use App\Token;
use App\FileSystem;

class RouterException extends \Exception {}

class Router extends Component {

    public function getController() {
    	if ('post' === $this->app->request()->method() && 'authenticate' === $this->app->request()->request()) return '\\App\\Token';
    	else if (!Token::isValid()) throw new RouterException('Invalid token', 401);
    	else if ('verify' === $this->app->request()->request()) {
    		$content = Json::encode(array('success' => true));
        	$this->app->response()->setContent($content);
        	$this->app->response()->send();
    	} else if (0 === strpos($this->app->request()->request(), FileSystem::MEDIAS_PATH)) return '\\App\\FileSystem';
        return '\\App\\Table';
    }
}