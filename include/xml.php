<?php

class xml extends DOMDocument
{
	public $root;
	public $root_name;
	public $last_element;

	public function __construct($version = "1.0", $encoding = "utf-8"){
		parent::__construct($version, $encoding);
		$this->root_name = "root";

	}

	public function setAttribute($code, $value, $elm = null){
		if(is_null($elm)){
			$elm = $this->last_element;
		}
		$attribute = $this->createAttribute($code);
		$elm->appendChild($attribute);

		$txt = $this->createTextNode($value);
		$attribute->appendChild($txt);
	}

	public function setRootAttribute($code, $value){
		$attribute = $this->createAttribute($code);
		$this->root->appendChild($attribute);

		$txt = $this->createTextNode($value);
		$attribute->appendChild($txt);
	}

	public function addNode($tag_name = "node", $value = "", $append_to_root = true, $append_to = null){

		if(!isset($this->root)){
			$element = $this->root = $this->createElement($this->root_name);
			$append = false;
		}else{
			if($tag_name == "CDATA"){
				$element = $this->createCDATASection($value);
			}else{
				$element = $this->createElement($tag_name, $value);
			}
			$append = true;
		}

		if($append){
			if($append_to_root){
				$this->root->appendChild($element);
			}else{
				if(is_null($append_to)){
					$append_to = $this->last_element;
				}

				$append_to->appendChild($element);
			}
		}else{
			$this->appendChild($element);
		}

		$this->last_element = $element;

		return $element;
	}

	public function render(){
		$code = '';

		$code .= $this->saveXML();

		return $code;
	}

}
?>