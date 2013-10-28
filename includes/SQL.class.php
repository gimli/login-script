<?php
if(!defined('IN_SCRIPT')){
   die("External access denied");
}

/**
 * PDO MySQL CLASS
 *  
 * @author Nickless
 * @link http://www.isengard.dk
 * @license Use how you like it, just please don't remove or alter this PHPDoc
 */  

class SQL{

  /**
   * The PDO instance
   *
   */
  static private $PDOInstance;

  /**
   * Creates a PDO instance representing a connection to a database and makes the instance available as a singleton
   * 
   * @param string $dsn The full DSN, eg: mysql:host=localhost;dbname=testdb
   * @param string $username The user name for the DSN string. This parameter is optional for some PDO drivers.
   * @param string $password The password for the DSN string. This parameter is optional for some PDO drivers.
   * @param array $driver_options A key=>value array of driver-specific connection options
   * 
   * @return PDO
   */
  public function __construct($dns, $username=false, $password=false, $driver_options=false){
    if(!self::$PDOInstance){
      try{
           self::$PDOInstance = new PDO($dns, $username, $password, $driver_options);
      }
      catch(PDOException $e){
        die("PDO CONNECTION ERROR: ".$e->getMessage()."\r\n");
      }
    }
    return self::$PDOInstance;
  }

  /**
  * Prepares a statement for execution and returns a statement object 
  *
  * @param string $statement A valid SQL statement for the target database server
  * @param array $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj 
returned  
  * @return PDOStatement
  */
  public function prepare($prepare, $driver_options=false){
    if(!$driver_options) 
       $driver_options=array();
    return self::$PDOInstance->prepare($prepare, $driver_options);
  }

  /**
  * Execute query
  *
  */
  public function execute(){
    return self::$PDOInstance->execute();
  }

  /**
  * Execute query and return one row in assoc array
  *
  * @return array
  */
  public function fetch(){
    return self::$PDOInstance->fetch();
  }

  /**
  * fetch current rowCount()
  *
  * @return array
  */
  public function rowCount(){
    return self::$PDOInstance->rowCount();
  }
}
?>
