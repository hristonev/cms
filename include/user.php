<?php

class user extends base{

	const LOGIN_ATTEMPTS = 5;
	protected $childClass;
	private $loginAttempt;

	public function __construct(){
		parent::__construct();

		if(!isset($_SESSION['userLog'])){
			$_SESSION['userLog'] = array();
			$_SESSION['userLog']['id'] = 0;
			$_SESSION['userLog']['loginAttempt'] = 0;
		}

		$this->loginAttempt = & $_SESSION['userLog']['loginAttempt'];
	}

	protected function isValid(){
		$valid = false;
		if(isset($_SESSION['userLog'])){
			$sql = new database();
			$sql->query("
				SELECT
					`user`.`cmsAccess`
				FROM
					`user`
				WHERE
					`user`.`userId` = ". (int)$_SESSION['userLog']['id']. "
			");
			if((int)$sql->cmsAccess === 1){
				$valid = true;
			}
		}

		return $valid;
	}

	protected function render(){
		$code = '';

		$code .= '<div class="login">';
		$code .= '<div class="loginRow">';
		$code .= '<div class="loginCell">'. $this->kwd('username'). '<div>';
		$code .= '<input class="loginCell" type="text" name="username" value="">';
		$code .= '<div class="loginCell">'. $this->kwd('password'). '<div>';
		$code .= '<input class="loginCell" type="password" name="password" value="">';
		$code .= '<div class="loginBtn"><a href="#" target="_self">'. $this->kwd('login'). '</a></div>';
		$code .= '<div class="loginMsg">'. $this->kwd('login attempt'). ' <span>'. (self::LOGIN_ATTEMPTS - (int)$this->loginAttempt). '</span></div>';
		$code .= '</div>';
		$code .= '</div>';


		return $code;
	}

	public function xLogout($arg, &$json){
		unset($_SESSION['userLog']);
		$json->msg = $this->kwd('logoutMsg');
	}

	public function xValidateLogin($arg, &$json){
		$json->loginAttempt = self::LOGIN_ATTEMPTS - (int)$this->loginAttempt;
		if($this->loginAttempt < 5){
			$this->loginAttempt++;
			$sql = new database();
			$sql->query("
				SELECT
					`user`.`userId`
					, `user`.`cmsAccess`
				FROM
					`user`
				WHERE
					`user`.`username` = '". $arg['username']. "'
				AND
					`user`.`password` = '". $arg['password']. "'
			");

			$_SESSION['userLog']['id'] = $sql->userId;
			$json->valid = $sql->cmsAccess;
			$json->include = 'builder';
		}
// 		$xml->addNode('loginAttempt', (self::LOGIN_ATTEMPTS - (int)$this->loginAttempt));
// 		if($this->loginAttempt < 5){
// 			$this->loginAttempt++;
// 			$sql = new database();
// 			$sql->query("
// 				SELECT
// 					`user`.`userId`
// 					, `user`.`cmsAccess`
// 				FROM
// 					`user`
// 				WHERE
// 					`user`.`username` = '". $arg['username']. "'
// 				AND
// 					`user`.`password` = '". $arg['password']. "'
// 			");

// 			$_SESSION['userLog']['id'] = $sql->userId;
// 			$xml->addNode('valid', $sql->cmsAccess);
// 			$xml->addNode('include', 'builder');
// 		}

	}
}

?>