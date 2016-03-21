<?php

class cmsNavigation extends user
{

	public function __construct(){
		parent::__construct();

		$this->childClass = & $this;
	}

	public function xGetData($arg, &$json){

		$json->navigation = array();
		$json->navigation['group'] = array();
		$group1 = &$json->navigation['group'][];
		$group2 = &$json->navigation['group'][];
		$group3 = &$json->navigation['group'][];
		$group4 = &$json->navigation['group'][];

		$group1 = new stdClass();
		$group2 = new stdClass();
		$group3 = new stdClass();
		$group4 = new stdClass();

		$group1->name = $this->kwd('favorite');
		$group2->name = $this->kwd('dbStruct');
		$group3->name = $this->kwd('settings');
		$group4->name = $this->kwd('administration');

		$sql = new database();
		$sql->query("SHOW TABLES");
		if($sql->num_rows() > 0){
			do{
				foreach ($sql->row as $value){
					$group = &$group2;
					if(strpos($value, 'sys') === 0){
						$group = &$group4;
					}
					if(!property_exists($group,'item')){
						$group->item = new stdClass();
					}
					if(strpos($value, TABLE_ML_SUFFIX)){
						$code = substr($value, 0, -strlen(TABLE_ML_SUFFIX));
						$group->item->$code->ml = true;
					}else{
						$group->item->$value = new stdClass();
						$group->item->$value->name = $this->kwd($value);
						$group->item->$value->ml = false;
					}
				}
			}while($sql->next());
		}

	}
}

?>