<?php
namespace App;

use Library\Application;
use Library\Component;
use Library\Utils\DB;
use Library\Utils\Json;
use PDO;

class TableException extends \Exception {}

class Table extends Component {

	private $table, $id = 0;
	private $fields = array();

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
    	$params = explode('/', $this->app->request()->request());
    	if (isset($params[0]) && is_string($params[0])) $this->table = array_shift($params);
    	else throw new TableException("No table in Request", 400);
    	while (count($params) > 0) {
    		$key = array_shift($params);
    		if (is_numeric($key) && $this->id == 0) $this->id = (int) $key;
    		else {
    			$value = count($params) > 0 ? array_shift($params) : 'NULL';
    			$_GET[$key] = $value;
    		}
    	}
    	if ($this->app->request()->method() == 'post' && $this->id > 0) {
    		throw new TableException('Request method error, could not POST with ID', 400);
    	} else if (($this->app->request()->method() == 'put' || $this->app->request()->method() == 'delete') && $this->id == 0) {
    		throw new TableException('Request method error, could not ' . strtoupper($this->app->request()->method()) . ' without ID', 400);
    	}

    	$qry = 'SHOW FIELDS FROM ' . $this->table;
    	$rs = DB::query($qry);
    	while($rw = $rs->fetch(PDO::FETCH_ASSOC)) 
    		$this->fields[$rw['field']] = preg_replace('@(\w+)\(\d+\)@', '$1', $rw['type']);
    }

    private function load() {
    	$result = array();
    	$qry = 'SELECT * FROM ' . $this->table;
    	$where = array();
    	if ($this->id > 0) $where[] = 'id=' . $this->id;
    	foreach ($_GET as $key => $value) {
    		if (in_array($key, array('id', 'order', 'asc', 'desc', 'limit'))) continue;
    		if (FALSE !== strpos($value, '%')) $where[] = $key . ' LIKE "' . $value . '"';
    		else $where[] = $key . '="' . $value . '"';
    	}
    	if (count($where) > 0) $qry .= ' WHERE ' . implode(' AND ', $where);
    	if (!empty($_GET['order'])) {
    		$qry .= ' ORDER BY ' . $_GET['order'];
    		if (isset($_GET['asc'])) $qry .= ' ASC';
    		if (isset($_GET['desc'])) $qry .= ' DESC';
    	}
    	if (!empty($_GET['limit'])) $qry .= ' LIMIT ' . $_GET['limit'];
    	//if (DEBUG) $result['query'] = $qry;
    	$rs = DB::query($qry);
    	if ($rs->rowCount() > 0) 
			while($rw = $rs->fetch(PDO::FETCH_ASSOC)) $result[] = $rw;
		return array('success' => true, 'result' => $result);
    }

    private function insert() {
    	$values = $this->values();
    	$qry = 'INSERT INTO ' . $this->table . ' SET ' . implode(', ', $values);
    	$result = array('success' => (bool) DB::exec($qry));
    	if ($result['success']) $result['id'] = (int) DB::lastInsertId();
    	return $result;
    }

    private function update() {
    	$values = $this->values();
    	$qry = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $values) . ' WHERE id=' . $this->id;
    	$result = array('success' => (bool) DB::exec($qry));
    	if ($result['success']) $result['id'] = $this->id;
    	return $result;
    }

    private function delete() {
    	$qry = 'DELETE FROM ' . $this->table . ' WHERE id=' . $this->id;
    	$result = array('success' => (bool) DB::exec($qry));
    	if ($result['success']) $result['id'] = $this->id;
    	return $result;
    }

    private function values() {
    	$values = array();
    	foreach ($_POST as $key => $value) {
    		if ($key == 'id') continue;
    		if (array_key_exists($key, $this->fields)) {
    			if ($this->fields[$key] != 'int') $value = '"' . $value . '"';
    			$values[] = $key . '=' . $value;
    		}
    	}
    	return $values;
    }
}