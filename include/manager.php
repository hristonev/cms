<?php

class manager extends user
{
	protected $headAddionional = array();

	public $ajax = false;

	private $framework = array('builder.js'
			,'ajax.js'
			,'event.js'
			,'xml.js'
			,'domElement.js'
			,'navigation.js'
			,'popUp.js'
			,'cmsHead.js'
			,'cmsNavigation.js'
			,'cmsView.js'
			,'cmsViewTab.js'
			,'DnD.js'
			,'cmsSiteMap.js'
			,'cmsSelectBox.js'
			,'log.js'
			,'calendar.js'
			,'ckeditor/ckeditor.js'
			,'animate.js'
			,'custom/fileManager.js'
			,'main.css');

	public function __construct(){
		parent::__construct();

		$this->childClass = & $this;
	}

	public function render(){
		$code = '';

		if(is_array($_POST) && count($_POST) > 0){
			$code .= $this->xmlhttp();
		}else if($this->isValid()){
			$this->headAddionional = $this->framework;

			$code .= $this->html('
			<script type="text/javascript">
				$(window).ready(function() {
					if(typeof(window.__builder) == "undefined"){
						var cms = new builder();
						cms.init();
					}
				});
			</script>
			');
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

		if(isset($_POST["argument"])){
			$arg = $_POST["argument"];
		}else{
			$arg = null;
		}

		if($this->isValid()){
			if(isset($_POST["group"])){
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
							$instance->$method($arg, $json);
							$code .= json_encode($json);
						}
				}
			}else{
				$json = new stdClass();
				include_once 'template/fileManager.php';
				fileManager::xUpload($_POST, $json);
				$code .= json_encode($json);
			}
		}else{
			$json = new stdClass();
			$this->xValidateLogin($arg, $json);
			if($this->isValid()){
				$json->framework = array();
				foreach ($this->framework as $key => $value){
					$item = & $json->framework[];
					$item = new stdClass();
					if(strpos($value, 'js')){
						$item->type = 'js';
						$item->value = $value;
					}
					if(strpos($value, 'css')){
						$item->type = 'css';
						$item->value = $value;
					}
				}
			}
			$code .= json_encode($json);
		}

		return $code;
	}
}

?>