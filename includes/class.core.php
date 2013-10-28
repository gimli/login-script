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

    // set $Sql using $this->db
    self::$Sql = $this->db;
    self::$Users = $this->Users;

  }

  /**
   * Return current value of $i in settings
   *
   * @return config
   */
  public static function GetConfig($i){
    return $this->Config[$i];
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

  public function __destruct() {
      $this->SQL = null;
  }
}
?>
