<?php

class object extends base
{
	private $name;
	private $dataCollection;
	private $objectCollection;
	private $languageId;

	public function __construct($name, $languageId = 0){
		parent::__construct();
		$this->name = $name;
		$this->languageId = (int)$languageId;
		$this->objectCollection = new stdClass();
		$this->objectCollection->select = array();
		$this->objectCollection->join = new stdClass();
		$this->objectCollection->fields = new stdClass();
		$this->objectCollection->where = new stdClass();
	}

	public function setDataCollectObject(&$data){
		$this->dataCollection = & $data;
	}

	public function initObject(){
		$sql = new database();

		$sql->query("
			SELECT
				`sys.dynamic`.*
			FROM
				`sys.dynamic`
			WHERE
				`sys.dynamic`.`tableName` = '". $this->name. "'
		");
		$this->objectCollection->global = $sql->row;
		//get structure for main object
		$this->getObjectFields($this->name);
		//get structure for ML object
		if(((int)$sql->isMultiLanguage)){
			$object = $this->name;
			$mlObject = $this->name. TABLE_ML_SUFFIX;
			$this->getObjectFields($mlObject);
			$this->objectCollection->join->$mlObject = "
				LEFT JOIN
					`". $mlObject. "`
				ON
					`". $mlObject. "`.`". $object. "Id` = `". $object. "`.`". $object. "Id`
			";
			//check for languageId
			if($this->languageId <= 0){
				$sql->query("
					SELECT
						`lang`.`langId`
					FROM
						`lang`
					WHERE
						`lang`.`default` = 1
				");
				$this->languageId = (int)$sql->langId;
			}
			$this->objectCollection->where->$mlObject = " AND `". $mlObject. "`.`langId` = ". $this->langId;
		}

// 		$this->dataCollection = $this->objectCollection;

		unset($sql);
	}

	public function getGridData(){
		$sql = new database();

		$this->dataCollection->dataGrid = new stdClass();
		$data = & $this->dataCollection->dataGrid;
		//get header data
		$data->row = array();
		$row = & $data->row[];
		$row = new stdClass();
		$row->type = 'header';
		$row->cell = array();
		foreach ($this->objectCollection->fields as $field => $value){
			if((int)$value->showInGrid == 1){
				$cell = & $row->cell[];
				$cell = new stdClass();
				if($value->primary){
					$cell->primary = true;
					$cell->name = $this->kwd('id');
				}else if($value->relation){
					$cell->name = $this->kwd($value->relateTable);
				}else{
					$cell->name = $this->kwd($value->field);
				}
				$cell->code = $value->field;
				$cell->width = $value->columnWidth;
				if((int)$value->isHeader == 1){
					$cell->type = 'header';
				}
			}
		}
		//get data set
		$this->collectData($this->dataCollection->dataGrid->row);
		unset($sql);
	}

	//RECURSIVE METHOD IF OBJECT IS TREE
	private function collectData(&$collector, $parentId = 0, $level = 1){
		$sql = new database();
		$sql->query($this->getDataQuery($parentId));
		if($sql->num_rows() > 0){
			do{
				$row = & $collector[];
				$row = new stdClass();
				$row->treeLevel = $level;
				$row->cell = array();
				foreach ($this->objectCollection->fields as $field => $value){
					if((int)$value->showInGrid == 1){
						$field = $value->field;
						$cell = & $row->cell[];
						$cell = new stdClass();
						if((int)$value->isHeader == 1){
							$cell->type = 'header';
						}
						if($value->primary){
							$cell->name = $sql->id;
						}else if($value->relation){
							$field = $value->relateTable;
							$cell->name = $sql->$field;
						}else{
							$cell->name = $sql->$field;
						}
					}
				}
				if((int)$this->objectCollection->global->isTree == 1){
					$this->collectData($collector, $sql->id, ($level + 1));
				}
			}while($sql->next());
		}
		unset($sql);
	}

	private function getDataQuery($parentId){
		$query = "";

		$query .= "SELECT 1";
		foreach ($this->objectCollection->select as $key => $value){
			if(isset($this->objectCollection->fields->$key) && (int)$this->objectCollection->fields->$key->showInGrid == 1){
				$query .= ", ". $value;
			}
		}
		$query .= " FROM `". $this->name. "`";
		foreach ($this->objectCollection->join as $key => $value){
			$query .= $value;
		}
		$query .= " WHERE";
		if((int)$this->objectCollection->global->isTree == 1){
			$query .= "`". $this->name. "`.`". $this->objectCollection->global->recursiveField. "` = ". $parentId;
		}else{
			$query .= " 1=1";
		}
		foreach ($this->objectCollection->where as $key => $value){
			$query .= $value;
		}
		if(strlen($this->objectCollection->global->order) > 0){
			$query .= " ORDER BY ". $this->objectCollection->global->order;
		}

		return $query;
	}

	private function getObjectFields($object){
		$sql = new database();

		$collectorJoin = & $this->objectCollection->fields;
		$collectorSelect = & $this->objectCollection->select;

		$sql->query("
			SELECT
				`sys.dependecies`.*
			FROM
				`sys.dependecies`
			WHERE
				`sys.dependecies`.`table` = '". $object. "'
		");
		if($sql->num_rows() > 0){
			do{
				$field = $sql->field;
				$sql->row->primary = false;
				$sql->row->relation = false;
				if(strlen($sql->relateTable) > 0 && strlen($sql->relateField) > 0){
					$sql->row->relation = true;
					$relateObject = $sql->relateTable;
					$this->objectCollection->join->$relateObject = "
						LEFT JOIN
							`". $relateObject. "`
						ON
							`". $relateObject. "`.`". $relateObject. "Id` = `". $this->name. "`.`". $relateObject. "Id`
					";
					$collectorSelect[$field] = "`". $relateObject. "`.`". $sql->relateField. "` as `$relateObject`";
				}else{
					if($sql->field == $object. "Id"){
						$field = "id";
						$sql->row->primary = true;
					}else{
						$field = $sql->field;
					}
					$collectorSelect[$field] = "`". $sql->table. "`.`". $sql->field. "` as `". $field. "`";
				}
				$collectorJoin->$field = $sql->row;
			}while($sql->next());
		}

		unset($sql);
	}

	public function collectHeaders(){

	}
}

?>