<?php
if(!defined('IN_SCRIPT')){
   die("External access denied");
}
class Core{

  public static $GetConfig;
  public static $Sql;
  public static $Users;

  public function __construct(){

    // Let's Start Session
    require_once("Session.class.php");
    $this->Session = SessionManager::sessionStart('username');

    // Let's import Config
    require_once("spyc.php");
    $this->Config = Spyc::YAMLLoad('./includes/settings.yaml');

    // require MySQL Class PDO
    require_once("SQL.class.php");

    // Let's connect
    try {
      $this->db = new SQL('mysql:host='.$this->Config['db_host'].';dbname='.$this->Config['db_name'],
                           $this->Config['db_user'],
                           $this->Config['db_pass'],
                           array(PDO::ATTR_PERSISTENT => true,
                                 PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    }
    catch (PDOException $e) {
      die($e->getMessage());
    }

    // set default language
    require_once("./lang/".$this->Config['default_language'].".lang.php");

    require_once("Users.class.php");
    $this->Users = new Users($this);


    // Add ISPConfig III API support / Needs server side setup
    // note we only load the connection for now, complete class will be added later.
    if($this->Config('useISPConfig') == "yes"){
      $this->Soap = new SoapClient(null, 
                                   array(
                                     'location' => $this->Config('soapLocation'),
                                     'uri' => $this->Confirg('soapUri'), 
                                     'trace' => 1, 
                                     'exceptions' => 1));
    }

    // set $Sql using $this->db
    self::$Sql = $this->db;
    self::$Users = $this->Users;
    self::$Soap = $this->Soap;

    // fire up plugin's if any and enabled in config.
    if($this->Config('enablePlugin') == "yes"){
      if(is_dir($this->Config('plugin_folder'))){
        if($files = opendir($this->Config('plugin_folder'))){
          while($name = readdir($files)){
            if($name != "."){
              if($name != ".."){
                require_once("./".$this->Config('plugin_folder')."/".$name);
                $this->$name = new $name($this);
                self::$name = $this->name;
              }
            }
          }
        }
      }
    }
    closedir($this->Config('plugin_folder'));

    //initialize the post variable
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
      $this->post = $_POST;
      if(get_magic_quotes_gpc ()) {
        //get rid of magic quotes and slashes if present
        array_walk_recursive($this->post, array($this, 'stripslash_gpc'));
      }
    }

    //initialize the get variable
    $this->get = $_GET;

    //decode the url
    array_walk_recursive($this->get, array($this, 'urldecode'));  
  }

  /**
   * Return current value of $i in settings
   *
   * @return config
   */
  public static function GetConfig($i){
    return $this->Config[$i];
  }

  /**/
  private function stripslash_gpc(&$value) {
    $value = stripslashes($value);
  }

  /**/
  private function htmlspecialcarfy(&$value) {
    $value = htmlspecialchars($value);
  }

  /**/
  protected function urldecode(&$value) {
    $value = urldecode($value);
  }

  /**
   * Redirects a user
   * @param $url
   */
  public function redirect($url) {
      switch ($this->Config['redirect_type']) {
          case 0:
              header("Location: {$url}");
              break;
          case 1:
              echo '<script type="text/javascript"><!-- window.location="' . $url . '"; //--></script>';
              break;
          case 2:
              echo '<html><head><meta http-equiv="refresh" value="0;' . $url . '" </head>';
              break;
      }
  }

  public function update_repo() {
    `git pull origin master`;
  }

  public function __destruct() {
    $this->SQL = null;
  }
}
?>
