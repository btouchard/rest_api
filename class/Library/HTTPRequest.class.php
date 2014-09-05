<?php
namespace Library;

class RequestException extends \Exception {}

class HTTPRequest {

    private $request, $method;
    private $input;

    public function __construct() {
        $req = str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']);
        $arr = explode('?', $req);
        $req = array_shift($arr);
        $this->request = trim($req, '/');
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->readInputs();
    }

    public function request() {
        return $this->request;
    }
    public function method() {
        return $this->method;
    }
    public function input() {
        return $this->input;
    }

    private function readInputs() {
        $this->input = file_get_contents("php://input");
        switch($this->method) {
            case 'post':
            case 'put':
                if (substr($this->input, 0, 1) === "{" || substr($this->input, 0, 1) === "[")
                    $_POST = json_decode($this->input, true);
                else if (strpos($this->input, "=") !== FALSE) {
                    parse_str($this->input, $this->input);
                    $_POST = $this->cleanInputs($this->input);
                }
                break;
            case 'get':
            case 'delete':
                $_GET = $this->cleanInputs($_GET);
                break;
            default:
                throw new RequestException(strtoupper($this->method) . " not authorized", 405);
                break;
        }
    }
    private function cleanInputs($data){
        $clean_input = array();
        if(is_array($data)){
            foreach($data as $k => $v){
                $clean_input[$k] = $this->cleanInputs($v);
            }
        }else{
            if(get_magic_quotes_gpc()){
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        return $clean_input;
    }
}