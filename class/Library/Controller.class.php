<?php
namespace Library;

use Library\Utils\Json;
use Library\Utils\StringUtils;

class ControllerException extends \Exception {}

class Controller extends Component {

    protected $module, $layout = 'default';

    public function __construct(Application $app) {
        parent::__construct($app);
        $this->module = $this->getModuleName();
    }

    public function __call($action, $args) {
        $this->{$action}($args);
        if ($this->app->request()->rest()) {
            $this->app->response()->addHeader('Content-Type: application/json; charset=utf-8;');
            $this->app->response()->setContent(Json::encode($this->vars));
        } else {
            $this->app->response()->addHeader('Content-Type: text/html; charset=utf-8;');
            $layout = new Layout($this->app, $this->module, $action, $this->layout);
            //if (!isset($this->vars['title'])) $this->vars['title'] = $this->module;
            foreach ($this->vars as $key => $value) $layout->set($key, $value);
            $this->app->response()->setContent($layout->draw());
        }
    }

    private function getModuleName() {
        $class = get_class($this);
        $tmp = explode('\\', $class);
        $class = array_pop($tmp);
        $module = str_replace('Controller', '', $class);
        if (StringUtils::endWith('s', $module) && !exist('\\App\\Model\\'.$module)) {
            $m = substr($module, 0, -1);
            if (exist('\\App\\Model\\'.$m)) $module = $m;
        }
        return $module;
    }
}