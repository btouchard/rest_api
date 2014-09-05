<?php
namespace App;

use Library\Application;
use Library\Component;
use Library\Utils\Json;

class FileSystemException extends \Exception {}

class FileSystem extends Component {

	const MEDIAS_PATH 	= "medias";
	const SEPARATOR 	= "/";

	private $path;
	private $recursive = false;

    public function run() {
    	$this->path = $this->app->request()->request();
    	switch ($this->app->request()->method()) {
    		case 'get': 	$result = $this->load(); 	break;
		    case 'post':	$result = $this->add(); 	break;
		    case 'put':		$result = $this->save();	break;
		    case 'delete':	$result = $this->delete(); 	break;
		}
    	$content = Json::encode($result);
        $this->app->response()->setContent($content);
        $this->app->response()->send();
    }

    private function load() {
    	if (!file_exists($this->path)) throw new FileSystemException("File not found", 400);
    	if (is_dir($this->path)) {
    		if (isset($_GET['recursive'])) $this->recursive = true;
    		$result = $this->dir();
    	} else if (is_file($this->path)) $result = $this->file();
    	return $result;
    }

    private function add() {
    	$result = array();
    	if (strpos($this->path, '/') < strpos($this->path, '.')) {
			$arr = explode(self::SEPARATOR, $this->path);
			$name = array_pop($arr);
			$path = implode(self::SEPARATOR, $arr);
		} else $path = $this->path;
    	if (!is_dir($path)) mkdir($path, 0777, true);
    	if (!is_dir($path)) throw new FileSystemException("Directory not found", 400);
    	foreach ($_FILES as $file) {
    		$filepath = $path . self::SEPARATOR . $file['name'];
    		$success = move_uploaded_file($file['tmp_name'], $filepath);
    		if ($success) {
	    		$entity = array('name' => $file['name'], 'path' => $filepath);
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$entity['mimetype'] = finfo_file($finfo, $filepath);
				$entity['size'] = filesize($filepath);
				$result[] = $entity;
    		}
    	}
    	return array('success' => true, 'result' => $result);
    }

    private function save() {
    	$success = false;
    	$result = array();
    	if (!file_exists($this->path)) throw new FileSystemException("File not found", 400);
    	if ($this->path == self::MEDIAS_PATH)  new FileSystemException("Could not RENAME files base direcctory '" . self::MEDIAS_PATH . "'", 400);
		$arr = explode(self::SEPARATOR, $this->path);
		$name = array_pop($arr);
		$path = implode(self::SEPARATOR, $arr);
    	if (!empty($_POST['name'])) {
    		$name = $_POST['name'];
    		$path = $path . self::SEPARATOR . $name;
    		$success = rename($this->path, $path);
    	} else if (!empty($this->app->request()->input())) {
    		$success = file_put_contents($this->path, $this->app->request()->input());
    	}
		$res = array('success' => $success);
		if ($success) {
			$this->path = $path;
			$result = array('name' => $name, 'path' => $this->path);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$result['mimetype'] = finfo_file($finfo, $this->path);
			$result['size'] = filesize($this->path);
		}
		if ($success) $res['result'] = $result;
    	return $res;
    }

    private function delete() {
    	if (!file_exists($this->path)) throw new FileSystemException("File not found", 400);
    	if ($this->path == self::MEDIAS_PATH)  new FileSystemException("Could not DELETE files base direcctory '" . self::MEDIAS_PATH . "'", 400);
    	if (is_file($this->path))
	    	$success = unlink($this->path);
	    else if (is_dir($this->path)) 
			$success = $this->delTree($this->path);
    	return array('success' => $success);
    }

    private function dir() {
    	$result = $this->files($this->path, $this->recursive);
    	return array('success' => true, 'result' => $result);
    }

    private function file() {
    	$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimetype = finfo_file($finfo, $this->path);
    	$content = file_get_contents($this->path);
        $this->app->response()->setContentType($mimetype);
        $this->app->response()->setContent($content);
        $this->app->response()->send();
    }

    private function files($path, $recursive) {
    	$files = $dirs = array();
    	$values = scandir($path, SCANDIR_SORT_ASCENDING);
    	array_shift($values);
    	array_shift($values);
    	for ($i=0; $i<count($values); $i++) {
    		$entity = array('name' => $values[$i], 'path' => $path . self::SEPARATOR . $values[$i]);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$entity['mimetype'] = finfo_file($finfo, $entity['path']);
    		if (is_dir($entity['path'])) {
    			$content = $this->files($entity['path'], $recursive);
    			$entity['fileCount'] = count($content);
    			if ($recursive) $entity['files'] = $content;
    			$dirs[] = $entity;
    		} else {
    			$entity['size'] = filesize($entity['path']);
				$files[] = $entity;
    		}
    	}
    	return array_merge($dirs, $files);
    }

	private function delTree($path) {
    	$values = scandir($path, SCANDIR_SORT_ASCENDING);
    	array_shift($values);
    	array_shift($values);
		foreach ($files as $file) {
			is_dir("$path/$file") ? $this->delTree("$path/$file") : unlink("$path/$file");
		}
		return rmdir($path);
	}
}