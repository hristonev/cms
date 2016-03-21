<?php
class base
{
	public static $xml;

	public $site_map_id;
	public $record_id;
	public $font_size;
	protected $langId;
	protected $parent;

	public function __construct(){
		if(!isset($this->langId)){
			$this->langId = 1;
		}
	}

	public function login(){

	}

	public function set_globals(&$obj){
		foreach ($obj as $key => $value){
			$this->$key = $value;
		}
	}

	public function get_lang_code(){
		$sql = new database();
		$sql->query("
			SELECT
				`sys.lang`.`code`
			FROM
				`sys.lang`
			WHERE
				`sys.lang`.`sys.langId` = ". (int)$this->langId. "
		");
		$value = $sql->code;
		unset($sql);

		return $value;
	}

	public function conf($code){
		$opt = new database();
		$cnf = new database();
		$cnf->query("
			SELECT
				`configGroup`.`name`
				, `config`.`value`
			FROM
				`config`
			JOIN `configGroup` ON `configGroup`.`configGroupId` = `config`.`configGroupId`
			WHERE
				`config`.`code` = '". $code. "'
		");
		$value = $cnf->value;
		switch ($cnf->name){
			case 'text':
				$opt->query("
					SELECT
						`kwdML`.`value`
					FROM
						`kwdML`
					WHERE
						`kwdML`.`kwdId` = ". (int)$value."
					AND
						`kwdML`.`langId` = ". $this->langId. "
				");
				$value = $opt->value;
				break;
		}
		unset($opt);
		unset($cnf);

		return $value;
	}

	public function kwd($code, $addSymbols = true){
		$cmsPrefix = '#';
		$kwd = new database();
		$kwd->query("
			SELECT
				`kwdML`.`value`
				, `kwd`.`kwdId` as `id`
			FROM
				`kwd`
			LEFT JOIN `kwdML` ON `kwdML`.`kwdId` = `kwd`.`kwdId`
			WHERE
				`kwd`.`code` = '". $cmsPrefix. $code. "'
		");
		$value = $kwd->value;
		if(empty($value)){
			if((int)$kwd->id <= 0){
				$kwd->exec("INSERT INTO `kwd` SET `kwd`.`code` = '". $cmsPrefix. $code. "'");
			}
			if($addSymbols){
				$value = "~". $code;
			}else{
				$value = $code;
			}
		}
		return $value;
	}

	public function xKwd($arg, &$json){
		$cmsPrefix = '#';
		$json->value = $this->kwd($arg["code"]);
	}

	public function globals($code){

	}

	public function checkJSON(){
		switch (json_last_error()) {
			case JSON_ERROR_NONE:

				break;
			case JSON_ERROR_DEPTH:
				dump(' - Maximum stack depth exceeded');
				break;
			case JSON_ERROR_STATE_MISMATCH:
				dump(' - Underflow or the modes mismatch');
				break;
			case JSON_ERROR_CTRL_CHAR:
				dump(' - Unexpected control character found');
				break;
			case JSON_ERROR_SYNTAX:
				dump(' - Syntax error, malformed JSON');
				break;
			case JSON_ERROR_UTF8:
				dump(' - Malformed UTF-8 characters, possibly incorrectly encoded');
				break;
			default:
				dump(' - Unknown error');
				break;
		}
	}

}

function error_handler($errno, $errstr, $errfile, $errline){
	$code = '';

	$errortype = array(
			E_ERROR              => 'Error',
			E_WARNING            => 'Warning',
			E_PARSE              => 'Parsing Error',
			E_NOTICE             => 'Notice',
			E_CORE_ERROR         => 'Core Error',
			E_CORE_WARNING       => 'Core Warning',
			E_COMPILE_ERROR      => 'Compile Error',
			E_COMPILE_WARNING    => 'Compile Warning',
			E_USER_ERROR         => 'User Error',
			E_USER_WARNING       => 'User Warning',
			E_USER_NOTICE        => 'User Notice',
			);
	if(isset($_GET["ajax"]) && (int)$_GET["ajax"] == 1){
		$error = base::$xml->add_node("error");
		$cdata = base::$xml->add_node("CDATA", $errstr, false, $error);
		$err_back = debug_backtrace();
		for($z = (count($err_back) - 1); $z >= 0; $z--){
			$backtrace = base::$xml->add_node("backtrace", "", false, $error);
			if(isset($err_back[$z]["line"])){
				base::$xml->set_attribute("line", $err_back[$z]["line"]);
			}
			if(isset($err_back[$z]["class"])){
				base::$xml->set_attribute("class", $err_back[$z]["class"]);
			}
			if(isset($err_back[$z]["function"])){
				base::$xml->set_attribute("method", $err_back[$z]["function"]);
			}
			if(isset($err_back[$z]["file"])){
				$cdata = base::$xml->add_node("CDATA", $err_back[$z]["file"], false, $backtrace);
			}
		}
	}else{
		$code .= '<div style="padding: 4px; color: #000000; background: #f1f1ff; font-family: Courier New; font-size: 11px; border: solid 2px #0000ff;">';
		$code .= '<div>';
		$code .= $errno. ': <br />';
		$code .= $errstr. '<br />';
		#$code .= 'in '. $errfile. '<br />';
		#$code .= 'on line '. $errline. '<br />';
		$code .= '</div>';

		$code .= '<ul type="number">';
		$err_back = debug_backtrace();
		for($z = (count($err_back) - 1); $z >= 0; $z--){


			if(isset($err_back[$z]["file"])){
				$code .= '<li><strong>'. $err_back[$z]["file"]. '</strong></li>';
			}

			$code .= '<ul>';

			if(isset($err_back[$z]["line"])){
				$code .= '<li>line: <strong>'. $err_back[$z]["line"]. '</strong></li>';
			}

			if(isset($err_back[$z]["class"])){
				$code .= '<li>class: <strong>'. $err_back[$z]["class"]. '</strong></li>';
			}

			if(isset($err_back[$z]["function"])){
				$code .= '<li>function: <strong>'. $err_back[$z]["function"]. '</strong></li>';
			}
			$code .= '</ul>';
		}
		$code .= '</ul>';
		$code .= '</div>';
	}
	if(isset($_GET["ajax"]) && (int)$_GET["ajax"] == 1){
		$_SESSION["error"][] = $code;
	}else{
		echo $code;
	}

}

function dump($object, $code = '', $head = ''){

	if($head == ''){
		$height = 10;
		echo '<div style="cursor: pointer; font-size: 11px; font-family: Courier New; background: #ffffff; width: 100%; border: solid 1px red; z-index: 1000; position: relative;">';
	}

	if(is_array($object)){
		echo $head;
		echo '<ul>';
		foreach($object as $key => $value){
			if(!is_array($value) && !is_object($value)){
				echo '<li>[ '. $key. ' ] : '. $value. '</li>';
			}else{
				echo '<pre>';
				echo dump($value, "", $key);
				echo '</pre>';
			}
		}
		echo '</ul>';
	}elseif(is_object($object)){
		echo $head;
		echo '<ul>';
		echo '<pre>';
		print_r($object);
		echo '</pre>';
		echo '</ul>';
	}else{
		echo $head;
		echo '<ul>';
		echo '<pre>';
		echo $object;
		echo '</pre>';
		echo '</ul>';
	}
	if($head == ''){
		echo '</div>';
	}
}

function err(){
	$debug += 1;
}
if(!defined("__CRON_JOB__") || constant("__CRON_JOB__") !== true){
	set_error_handler("error_handler");
}

?>