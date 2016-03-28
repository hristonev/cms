<?php

class manager extends user
{
	protected $headAddionional = array();
	public $ajax = false;

	public function __construct(){
		parent::__construct();

		$this->childClass = & $this;
	}

	public function render(){
		$code = '';
// 		this.loadModule = new Array(
// 				"ajax",
// 				"event",
// 				"xml",
// 				"domElement",
// 				"list",
// 				"navigation",
// 				"popUp",
// 				"cmsHead",
// 				"cmsNavigation",
// 				"cmsView",
// 				"cmsViewTab",
// 				"cmsSiteMap",
// 				"cmsSelectBox",
// 				"log"
// 				);
// 		this.loadNonEssentialModule = new Array(
// 				"ckeditor/ckeditor"
// 				, "animate"
// 				);
		if(is_array($_POST) && count($_POST) > 0){
			$code .= $this->xmlhttp();
		}else if($this->isValid()){
			$this->headAddionional[] = 'builder.js';
			$this->headAddionional[] = 'ajax.js';
			$this->headAddionional[] = 'event.js';
			$this->headAddionional[] = 'xml.js';
			$this->headAddionional[] = 'domElement.js';
			$this->headAddionional[] = 'list.js';
			$this->headAddionional[] = 'navigation.js';
			$this->headAddionional[] = 'popUp.js';
			$this->headAddionional[] = 'cmsHead.js';
			$this->headAddionional[] = 'cmsNavigation.js';
			$this->headAddionional[] = 'cmsView.js';
			$this->headAddionional[] = 'cmsViewTab.js';
			$this->headAddionional[] = 'cmsSiteMap.js';
			$this->headAddionional[] = 'cmsSelectBox.js';
			$this->headAddionional[] = 'log.js';
			$this->headAddionional[] = 'ckeditor/ckeditor.js';
			$this->headAddionional[] = 'animate.js';
			$this->headAddionional[] = 'main.css';
			$code .= $this->html("");
		}else{
			$code .= $this->html(parent::render());
		}

		return $code;
	}

	private function html($template){
		$this->childClass->headAddionional[] = 'user.js';
		$this->childClass->headAddionional[] = 'user.css';

		$code = '<!DOCTYPE html>';
		$code .= '<html lang="en">';
		$code .= '<head>';
		$code .= '<meta charset="utf-8">';
		$code .= '<title>'. $this->kwd('cmsTitle'). '</title>';
		$code .= '<link rel="stylesheet" type="text/css" media="screen" href="../css/font-awesome.min.css" />';
		$code .= '<script src="javascript/jquery.js" type="text/javascript"></script>';
		$code .= '<script src="javascript/sha512.js" type="text/javascript"></script>';
		$code .= '<script type="text/javascript">window.__base = new Array();</script>';
		foreach ($this->headAddionional as $value){
			if(strpos($value, 'js')){
				$code .= '<script src="javascript/'. $value. '" type="text/javascript"></script>';
			}
			if(strpos($value, 'css')){
				$code .= '<link rel="stylesheet" type="text/css" media="screen" href="css/'. $value. '" />';
			}
		}
		$code .= '</head>';
		$code .= '<body>';
		$code .= $template;
		$code .= '</body>';
		$code .= '</html>';
		return $code;
	}

	private function xmlhttp(){
		$code = '';
		$this->ajax = true;
		switch ($_POST["group"]){
			case 'js':
				if($this->isValid()){
					$code .= file_get_contents("javascript/". $_POST['className']. ".js");
				}
				break;
			case 'xml':
				header("content-type: text/xml");
				if(isset($_POST["className"]) && isset($_POST["className"]) && !empty($_POST["className"]) && !empty($_POST["methodName"])){
					self::$xml = new xml();
					include_once $_POST["group"]. "/". $_POST["className"]. ".php";
					$method = $_POST["methodName"];
					$class = $_POST["className"];
					$instance = new $class();
					self::$xml->addNode($class);
					if(isset($_POST["argument"])){
						$arg = $_POST["argument"];
					}else{
						$arg = null;
					}
					$instance->$method($arg, self::$xml);
					$code .= self::$xml->render();
				}
				break;
			default:
				if(isset($_POST["className"]) && isset($_POST["className"]) && !empty($_POST["className"]) && !empty($_POST["methodName"])){
					$json = new stdClass();
					include_once $_POST["group"]. "/". $_POST["className"]. ".php";
					$method = $_POST["methodName"];
					$class = $_POST["className"];
					$instance = new $class();
					if(isset($_POST["argument"])){
						$arg = $_POST["argument"];
					}else{
						$arg = null;
					}
					$instance->$method($arg, $json);
					$code .= json_encode($json);
				}
		}

		return $code;
	}
}

?>