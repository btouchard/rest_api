<?php
namespace Mongo;

use Library\Component;
use Library\Utils\Json;
use Library\Utils\MongoDB;

class TableException extends \Exception {}

class Table extends Component {

    public static $PROTECTED = array('app_user');

	private $table, $id = 0;
	private $params = array();

    public function run() {
    	$this->prepare();
    	switch ($this->app->request()->method()) {
    		case 'get': 	$result = $this->load(); 	break;
    		case 'post': 	$result = $this->insert(); 	break;
    		case 'put': 	$result = $this->update(); 	break;
    		case 'delete': 	$result = $this->delete(); 	break;
    		default: $result = array('success' => false);
    	}
    	$content = Json::encode($result);
        $this->app->response()->setContent($content);
        $this->app->response()->send();
    }

    private function prepare() {
    	$this->params = explode('/', $this->app->request()->request());
    	if (isset($this->params[0]) && is_string($this->params[0])) $this->table = array_shift($this->params);
    	else throw new TableException("No table in Request", 400);
        if (in_array($this->table, self::$PROTECTED)) throw new TableException("This table in not accessible for security reason", 400);
        if (isset($_GET['id'])) $this->id = (int) $_GET['id'];
    }

    private function parseRow($row) {
        foreach ($row as $field => $value) {
            if ($value instanceof \MongoId) $row[$field] = $value->{'$id'};
            else if ($value instanceof \MongoDate) $row[$field] = $value->sec;
        }
        return $row;
    }

    private function load() {
        $result = array();
        $values = $this->values($_GET);
        //print_r($values);
        //exit;
    	$cursor = MongoDB::getInstance()->{$this->table}->find($values);
        foreach ($cursor as $row) $result[] = $this->parseRow($row);
		return array('success' => true, 'result' => $result);
    }

    private function insert() {
        $result = array('success' => false);
    	$values = $this->values($_POST);
    	$new = MongoDB::getInstance()->{$this->table}->insert($values);
        //var_dump($new);
        if (!empty($new->errmsg)) $result['result'] = $new->errmsg;
        else {
            $result['success'] = (bool) $new['ok'];
            if ($result['success']) $result['_id'] = $values['_id']->{'$id'};
        }
    	return $result;
    }

    private function update() {
    	$values = $this->values($_POST);
    	$qry = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $values) . ' WHERE id=' . $this->id;
    	$result = array('success' => (bool) DB::exec($qry));
    	if ($result['success']) $result['id'] = $this->id;
    	return $result;
    }

    private function delete() {
        $remove = MongoDB::getInstance()->{$this->table}->remove($_GET);
    	$result = array('success' => (bool) $remove);
    	return $result;
    }

    private function values($data) {
    	$values = array();
    	foreach ($data as $key => $value) {
            if (empty($value) && strpos($key, '>') !== false) {
                list($key, $value) = explode('>', $key);
                $values[$key] = array('$gt' => $value);
            }
            else if ($key == '_id') $values['_id'] = new \MongoId($value);
            else if (is_array($value)) $values[$key] = $this->values($value);
            else if ($key == 'date' || $key == 'expire' || $key == 'time') $values[$key] = new \MongoDate($value);
            else if (is_int($value)) $values[$key] = (int) $value;
            else $values[$key] = $value;
    	}
    	return $values;
    }
}