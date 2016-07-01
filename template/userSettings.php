<?php
class userSettings extends user
{
	private $dataCollection;

	public function __construct(){
		parent::__construct();
	}

	public function setDataCollectObject(&$data){
		$this->dataCollection = & $data;
	}

	public function render(){
		$user = & $this->dataCollection->user;
		$user = new stdClass();
		$user->valid = $this->isValid();
		$user->name = $this->getUserName();
		$user->save = $this->kwd('save');
		$user->accountSettings = $this->kwd('accountSettings');
		$user->currentPassword = $this->kwd('currentPassword');
		$user->newPassword = $this->kwd('newPassword');
		$user->newPasswordRepeat = $this->kwd('newPasswordRepeat');
		$user->changeUsername = $this->kwd('changeUsername');
		$user->addUser = $this->kwd('addUser');
		$user->username = $this->kwd('username');
		$user->password = $this->kwd('password');
		$user->cmsAccess = $this->kwd('cmsAccess');
	}

	public function xChangeAccount($arg, &$json){
		$formData = json_decode($arg['formData']);
		$json->error = array();
		$json->info = array();
		$continue = true;
		if(
			empty($formData->currentPassword)
			&& empty($formData->newPassword)
			&& empty($formData->newPasswordRepeat)
			&& empty($formData->changeUsername)){
				$json->error[] = $this->kwd('NoDataPresent');
				$continue = false;
		}
		if($continue && empty($formData->currentPassword)){
			$json->error[] = $this->kwd('passwordIsEmpty');
			$continue = false;
		}
		if($continue && !$this->checkValidPassword($formData->currentPassword)){
			$json->error[] = $this->kwd('invalidPassword');
			$continue = false;
		}
		//username
		if($continue && !empty($formData->changeUsername)){
			if($this->checkAvailableUsername($formData->changeUsername)){
				if($this->changeUserName($formData->currentPassword, $formData->changeUsername)){
					$json->info[] = $this->kwd('usernameSuccessfulyChanged');
					$json->info[] = $this->kwd('pleaseUseNewUsernameNextLogon');
				}else{
					$json->error[] = $this->kwd('usernameNotChanged');
				}
			}else{
				$json->error[] = $this->kwd('usernameNotAvailable');
			}
		}
		//password
		if($continue && !empty($formData->newPassword) && $formData->newPassword === $formData->newPasswordRepeat){
			if($this->changePassword($formData->currentPassword, $formData->newPassword)){
				$json->info[] = $this->kwd('passwordSuccessfulyChanged');
				$json->info[] = $this->kwd('pleaseUseNewPasswordNextLogon');
			}else{
				$json->error[] = $this->kwd('passwordNotChanged');
			}
		}elseif($continue && (!empty($formData->newPassword) || !empty($formData->newPasswordRepeat))){
			$json->error[] = $this->kwd('passwordNotMatch');
			$continue = false;
		}
	}

	public function xAddAccount($arg, &$json){
		$formData = json_decode($arg['formData']);
		$json->error = array();
		$json->info = array();
		if(empty($formData->username) || empty($formData->password)){
			$json->error[] = $this->kwd('NoDataPresent');
		}else{
			if($this->checkAvailableUsername($formData->username)){
				if($this->addUser($formData->username, $formData->password, $formData->cmsAccess)){
					$json->info[] = $this->kwd('userSuccessfullyAdded');
				}else{
					$json->error[] = $this->kwd('userNotAdded');
				}
			}else{
				$json->error[] = $this->kwd('usernameNotAvailable');
			}
		}
	}
}