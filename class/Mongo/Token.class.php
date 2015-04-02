<?php
namespace Mongo;

use Library\Utils\MongoDB;
use Library\Utils\Json;
use Library\Component;
use MongoDate;

class Token extends Component {
    private static $token = null;

    public static function isValid() {
        global $config;
        $headers = getallheaders();
        Token::$token = null;
        if (!empty($headers[AUTH_HEADER])) Token::$token = $headers[AUTH_HEADER];
        else if (DEBUG && !empty($_REQUEST['token'])) Token::$token = $_REQUEST['token'];
        if (!empty(Token::$token)) {
            if (isset($_SESSION['token']) && $_SESSION['token'] === Token::$token) return true;
            $params = array('token' => Token::$token, 'expire' => array('$gt' => new MongoDate()));
            $cursor = MongoDB::getInstance()->app_user->find($params);
            $user = $cursor->getNext();
            if ($user != null) {
                Token::$token = $user['token'];
                $expire = time() + (24 * 60 * 60);
                $success = MongoDB::getInstance()->app_user->update(array('token' => Token::$token), array('$set' => array('expire' => new MongoDate($expire))));
                if ($success) {
                    $_SESSION['token'] = Token::$token;
                    return true;
                }
            }
        }
        return false;
    }

    public function run() {
        switch ($this->app->request()->request()) {
            case 'signin':  $result = $this->signin(); break;
            case 'signout': $result = $this->signout(); break;
            case 'me':      $result = $this->me(); break;
        }
        $content = Json::encode($result);
        $this->app->response()->setContent($content);
        $this->app->response()->send();
    }

    private function signin() {
        global $config;
        $result['success'] = false;
        if (empty($_POST['email'])) $result['error'] = 'Email is needed';
        else if (empty($_POST['password'])) $result['error'] = 'Password is needed';
        else {
            $params = array('email' => $_POST['email'], 'password' => $_POST['password']);
            $cursor = MongoDB::getInstance()->app_user->find($params);
            $user = $cursor->getNext();
            if ($user == null) {
                $result['error'] = 'Invalid Email or Password';
            } else {
                if (empty($user['token']) || time() > strtotime($user['expire'])) {
                    $user['token'] = uniqid();
                    $user['expire'] = time() + (24 * 60 * 60);
                    $success = MongoDB::getInstance()->app_user->update(array('_id' => $user['_id']), array('$set' => array('token' => $user['token'], 'expire' => new MongoDate($user['expire']))));
                    if ($success) {
                        $_SESSION['token'] = $user['token'];
                    }
                    $result['success'] = true;
                    $result['result']['token'] = $user['token'];
                }
            }
        }
        return $result;
    }

    private function signout() {
        global $config;
        $result['success'] = false;
        $success = MongoDB::getInstance()->app_user->update(array('token' => Token::$token), array('$set' => array('token' => null)));
        if ($success) {
            $result['success'] = true;
            $_SESSION['token'] = null;
        }
        return $result;
    }

    private function me() {
        global $config;
        $result['success'] = false;
        $cursor = MongoDB::getInstance()->app_user->find(array('token' => Token::$token));
        $user = $cursor->getNext();
        if ($user != null) {
            //var_dump($user);
            unset($user['token'], $user['password'], $user['expire']);
            $user['_id'] = $user['_id']->{'$id'};
            $result['success'] = true;
            $result['result'] = $user;
        }
        return $result;
    }
}