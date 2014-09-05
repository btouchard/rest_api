<?php
namespace Library\Utils;

use PDO;

class DB {
   
    private static $objInstance;
   
    /*
     * Class Constructor - Create a new database connection if one doesn't exist
     * Set to private so no-one can create a new instance via ' = new DB();'
     */
    private function __construct() {}

    /*
     * Like the constructor, we make __clone private so nobody can clone the instance
     */
    private function __clone() {}
   
    /*
     * Returns DB instance or create initial connection
     * @param
     * @return $objInstance;
     */
    public static function getInstance(  ) {
           
        if(!self::$objInstance){
			self::$objInstance = new PDO('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DB, MYSQL_USER, MYSQL_PASS);
			self::$objInstance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER); // les noms de champs seront en caractères minuscules
			self::$objInstance->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION); // les erreurs lanceront des exceptions
			self::$objInstance->exec("SET NAMES 'utf8'");
        }
       
        return self::$objInstance;
   
    } # end method
   
    /*
     * Passes on any static calls to this Library onto the singleton PDO instance
     * @param $chrMethod, $arrArguments
     * @return $mix
     */
    final public static function __callStatic( $chrMethod, $arrArguments ) {
           
        $objInstance = self::getInstance();
        
		try {
	        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);
		} catch (PDOException $e) {
			echo "<pre>";
			print_r ($e->getTrace());
			echo "</pre>";
			exit();
		}
       
    } # end method
   
}
?>