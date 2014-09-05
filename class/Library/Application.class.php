<?php
namespace Library;

use Library\Utils\Json;

class Application {

    protected $request, $response;

    public function __construct() {
        try {
            $this->response = new HTTPResponse();
            $this->request = new HTTPRequest();
        } catch (RequestException $e) {
            $this->setError($e->getCode(), $e->getMessage());
        }
    }

    public function request() {
        return $this->request;
    }
    public function response() {
        return $this->response;
    }

    protected function setError($code, $content = null) {
        $this->response->setStatus($code);
        if (empty($content)) $content = $this->response->getCodeInfo($code);
        $content = Json::encode(array('success' => false, 'result' => $content));
        $this->response->setContent($content);
        $this->response->send();
    }
}