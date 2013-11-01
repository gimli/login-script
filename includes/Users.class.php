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

  public function userLogin($email, $password, $check){
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

      $ip = self::determineIP();
      $r = self::loadObject($r);

      if(!$r->id){ die("Something went wrong!"); }

      $_SESSION['status'] = 'allowed';
      $_SESSION['member'] = $r->username;
      $_SESSION['member_id'] = $r->id;

      $d = $this->db->prepare("UPDATE `account` SET `status` = 1 WHERE `id` = ".$r->id);
      $d->execute();

      $d = $this->db->prepare("UPDATE `account` SET `last_ip` = '$ip' WHERE `id` = ".$r->id);
      $d->execute();

      if($check){
        setcookie('status', 'allowed', time() + 1*24*60*60);
        setcookie('member', $r->username, time() + 1*24*60*60);
        setcookie('member_id', $r->id, time() + 1*24*60*60);
      }else{
        setcookie('status', '', time() + 1*24*60*60);
        setcookie('member', '', time() + 1*24*60*60);
        setcookie('member_id', '', time() + 1*24*60*60);
      }
      $this->Core->redirect("index.php?login=success");      
    }else{
      die(WRONG_USER_PASS);
    }
  }

  public function userLogout(){
    // destroy old session
    session_destroy();

    //Unset $_SESSION
    unset($_SESSION['status']);
    unset($_SESSION['member']);
    unset($_SESSION['member_id']);
    unset($_SESSION['password']);

    // Destroy old cookies
    setcookie('status', '', time() - 1*24*60*60);
    setcookie('member', '', time() - 1*24*60*60);
    setcookie('member_id', '', time() - 1*24*60*60);

    // Return to main page
    $this->Core->redirect("index.php?e=logout_success");
  }

  public function userRegister($email, $username, $password){
    $salt = self::random_string(10);

    $check_user = $this->db->prepare("SELECT * FROM `account` WHERE `email` = '$email'");
    $check_user->execute();
    $check = $check_user->fetch();
    $check = self::loadObject($check);

    if($email == $check->email){ $this->Core->redirect("login.php?e=email_addr_exsist"); }
    if($username == $check->username){ $this->Core->redirect("login.php?e=username_exsist"); }

    $hash = self::generatePasswordHash($password,$salt);

    unset($password);

    $ip = self::determineIP();

    // Inject user 
    $new_user = $this->db->prepare("INSERT INTO `account` (`email`,`username`,`md5_encrypted_password`,`salt`,`last_ip`) VALUES ('$email','$username','$hash','$salt','$ip')");
    $new_user->execute();
    if($new_user->rowCount() != 0) { $this->Core->redirect('login.php?e=register_success'); }
    else
    { $this->Core->redirect('login.php?e=register_failed'); }
  }

  public function isLogged(){
    if(isset($_SESSION['member_id'])){
      return true;
    }
  }

  public function userInfo(){
    if(isset($_SESSION['member_id'])){
      $u = $this->db->prepare("SELECT * FROM `account` WHERE `id` = ".$_SESSION['member_id']);
      $u->execute();
      if(!$u->rowCount()){
        die("Error: Unable to locate user id: ".$_SESSION['member_id']);
      }
      $r = $u->fetch();
      $r = self::loadObject($r);
      return $r;
    }
  }

  public function banCheck($type,$i){
    if($type == "account"){
      $d = $this->db->prepare("SELECT * FROM `account_banned` WHERE `id` = '$i'");
      $d->execute();
      if($d->rowCount() > 0){
        die(ACCOUNT_BANNED);
      }
    }else{
      $d = $this->db->prepare("SELECT * FROM `ip_banned` WHERE `ip` = '$i'");
      $d->execute();
      if($d->rowCount() > 0){
        die(IP_BANNED);
      }
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

  function checkIP($ip) {
	if (!empty($ip) && ip2long($ip) != -1 && ip2long($ip) != false) {
		$private_ips = array (
			array('0.0.0.0','2.255.255.255'),
			array('10.0.0.0','10.255.255.255'),
			array('127.0.0.0','127.255.255.255'),
			array('169.254.0.0','169.254.255.255'),
			array('172.16.0.0','172.31.255.255'),
			array('192.0.2.0','192.0.2.255'),
			array('192.168.0.0','192.168.255.255'),
			array('255.255.255.0','255.255.255.255')
		);
 
		foreach ($private_ips as $r) {
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	} else {
		return false;
	}
  }
 
  public static function determineIP() {
	if (!empty($_SERVER["HTTP_CLIENT_IP"]) && $this->checkIP($_SERVER["HTTP_CLIENT_IP"])) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		foreach (explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
			if ($this->checkIP(trim($ip))) {
				return $ip;
			}
		}
	}
	if (!empty($_SERVER["HTTP_X_FORWARDED"]) && $this->checkIP($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} elseif (!empty($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"]) && $this->checkIP($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])) {
		return $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
	} elseif (!empty($_SERVER["HTTP_FORWARDED_FOR"]) && $this->checkIP($_SERVER["HTTP_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_FORWARDED_FOR"];
	} elseif (!empty($_SERVER["HTTP_FORWARDED"]) && $this->checkIP($_SERVER["HTTP_FORWARDED"])) {
		return $_SERVER["HTTP_FORWARDED"];
	} elseif (!empty($_SERVER["REMOTE_ADDR"])) {
		return $_SERVER["REMOTE_ADDR"];
	} else {
		return false;
	}
  }

  public function random_string($counts){
    if(!$counts){ $counts = 10; }
    $str = "abcdefghijklmnopqrstuvwxyz1234567890";
    for($i=0;$i<$counts;$i++){
      if($o == 1){ $output .= rand(0,9); $o = 0; }
      else{ $o++; $output .= $str[rand(0,25)]; }
    }
          return $output;
  }

}
