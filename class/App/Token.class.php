<?php
namespace App;

use PDO;
use PDOException;
use Library\Utils\DB;
use Library\Utils\Json;
use Library\Component;

class Token extends Component {
    private static $token = null;
    private static $user = array();

    public static function isValid() {
        global $config;
        $headers = getallheaders();
        Token::$user = array();
        Token::$token = null;
        if (!empty($headers[AUTH_HEADER])) Token::$token = $headers[AUTH_HEADER];
        else if (DEBUG && !empty($_REQUEST['token'])) Token::$token = $_REQUEST['token'];
        if (!empty(Token::$token)) {
            if (isset($_SESSION['token']) && $_SESSION['token'] === Token::$token) return true;
            $qry = "SELECT `u`.`id` FROM `" . $config['auth']['mysql_table'] . "` AS  `u` WHERE `u`.`" . $config['auth']['mysql_token'] . "` = '" . Token::$token . "' AND `u`.`" . $config['auth']['mysql_expire'] . "` > NOW() LIMIT 0 , 1";
            $rs = DB::query($qry);
            if ($rs->rowCount() == 1) {
                Token::$user = $rs->fetch(PDO::FETCH_ASSOC);
                $expire = date("Y-m-d H:i:s", time() + (24 * 60 * 60));
                $qry = "UPDATE user SET expire='" . $expire . "' WHERE id = " . Token::$user['id'];
                if (DB::exec($qry)) {
                    $_SESSION['token'] = Token::$token;
                    return true;
                }
            }
        }
        return false;
    }

    public function run() {
        global $config;
        $result['success'] = false;
        if (empty($_POST[$config['auth']['mysql_user']])) $result['error'] = ucwords($config['auth']['mysql_user']) . ' is needed';
        else if (empty($_POST[$config['auth']['mysql_pass']])) $result['error'] = ucwords($config['auth']['mysql_pass']) . ' is needed';
        else {
            $qry = "SELECT `u`.`id`, `u`.`" . $config['auth']['mysql_token'] . "`, `u`.`" . $config['auth']['mysql_expire'] . "` FROM  `" . $config['auth']['mysql_table'] . "` AS  `u` WHERE `u`.`" . $config['auth']['mysql_user'] . "` = '" . $_POST[$config['auth']['mysql_user']] . "' AND `u`.`" . $config['auth']['mysql_pass'] . "` = '" . $_POST[$config['auth']['mysql_pass']] . "' LIMIT 0 , 1";
            $rs = DB::query($qry);
            if ($rs && $rs->rowCount() == 1) {
                $user = $rs->fetch(PDO::FETCH_ASSOC);
                if (empty($user[$config['auth']['mysql_token']]) || time() > strtotime($user[$config['auth']['mysql_expire']])) {
                    $user[$config['auth']['mysql_token']] = uniqid();
                    $user[$config['auth']['mysql_expire']] = date("Y-m-d H:i:s", time() + (24 * 60 * 60));
                    $qry = "UPDATE " . $config['auth']['mysql_table'] . " SET " . $config['auth']['mysql_token'] . "='" . $user['token'] . "', " . $config['auth']['mysql_expire'] . "='" . $user['expire'] . "' WHERE id = " . $user['id'];
                    if (DB::exec($qry)) {
                        $_SESSION['token'] = $user[$config['auth']['mysql_token']];
                    }
                }
                $result['success'] = true;
                $result['result']['token'] = $user['token'];
            } else {
                $result['error'] = 'Invalid ' . ucwords($config['auth']['mysql_user']) . ' or ' . ucwords($config['auth']['mysql_pass']);
            }
        }
        $content = Json::encode($result);
        $this->app->response()->setContent($content);
        $this->app->response()->send();
    }
}