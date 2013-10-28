<?php
if(!defined('IN_SCRIPT')){
   die("External access denied");
}

class Users extends Core{

  public $id;
  public $Core;

  // Let's pull our PDO MySQL class in
  function __construct(&$Core){
    $this->Core = &$Core;
    $this->db = $this->Core->db;
  }

  public function userLogin($email, $password){
    $p = $this->db->prepare("SELECT * FROM `account` WHERE `email` = '$email'");
    $p->execute();
    $r = $p->fetch();

    $salt = $r['salt'];

    $password = self::generatePasswordHash($password, $salt);
    $q = $this->db->prepare("SELECT * FROM `account` WHERE `email` = '$email' AND `md5_encrypted_password` = '$password'");
    $q->execute();
    $r = $q->fetch();
    if($q->rowCount() != 0){
      unset($password);


      $r = self::loadObject($r);

      if(!$r->id){ die("Something went wrong!"); }

      $_SESSION['status'] = 'allowed';
      $_SESSION['member'] = $r->username;
      $_SESSION['member_id'] = $r->id;

      $d = $this->db->prepare("UPDATE `account` SET `status` = 1 WHERE `id` = '".$r->id."'");
      $d->execute();

      if($remember){
        setcookie('status', 'allowed', time() + 1*24*60*60);
        setcookie('member', $r->username, time() + 1*24*60*60);
        setcookie('member_id', $r->id, time() + 1*24*60*60);
      }else{
        setcookie('status', '', time() + 1*24*60*60);
        setcookie('member', '', time() + 1*24*60*60);
        setcookie('member_id', '', time() + 1*24*60*60);
      }
      echo "Succes!\r\n";
      $this->Core->redirect("index.php?login=success");      
    }else{
      die(WRONG_USER_PASS."\r\n");
    }
  }

  public function userRegister($email, $username, $password){
    $salt = "";
  }

  public function isLogged(){
    if(isset($_SESSION['member_id'])){
      return true;
    }
  }

 /**
  * @param array()
  * @return object
  */
  private static function loadObject($array){
    return $object = new ArrayObject($array, ArrayObject::ARRAY_AS_PROPS);
  }

 /**
  * @param $password
  * @param $salt
  * @return string
  */
  private static function generatePasswordHash($password, $salt){
    return md5(sha1($password, $salt));
  }

  public function lockIp(){
    return $ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['HTTP_CLIENT_IP'];
  }
}
