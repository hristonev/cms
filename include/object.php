<?php

class object extends base
{
	private $name;
	private $dataCollection;
	private $objectCollection;
	private $languageId;
	private $recordView;
	private $customTemplate;
	private $maxTreeLevel;
	private $hasWeight;

	public function __construct($name = null, $languageId = 0){
		parent::__construct();
		if(!is_null($name)){
			$this->name = $name;
			$this->languageId = (int)$languageId;
			$this->objectCollection = new stdClass();
			$this->objectCollection->select = array();
			$this->objectCollection->join = new stdClass();
			$this->objectCollection->fields = new stdClass();
			$this->objectCollection->where = new stdClass();
			$this->recordView = false;
		}
	}

	public function isCustom(){
		return ($this->customTemplate == '') ? false : true;
	}

	public function getTemplate(){
		$class = $this->customTemplate;
		if(file_exists("template/". $class. ".php")){
			include_once "template/". $class. ".php";
		}
		if(class_exists($class)){
			$template = new $class();
			if(method_exists($template, 'render')){
				$template->setDataCollectObject($this->dataCollection);
				$template->render();
			}
		}
		return $class;
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
		$this->customTemplate = $sql->customTemplate;
		$this->objectCollection->global = $sql->row;
		$this->hasWeight = ($sql->weightField != "") ? true : false;
		//get structure for main object
		$this->getObjectFields($this->name);
		//get structure for ML object
		if(((int)$sql->isMultiLanguage)){
			$object = $this->name;
			$mlObject = $this->name. TABLE_ML_SUFFIX;
			$this->getObjectFields($mlObject, true);
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
			$this->objectCollection->where->$mlObject = " AND (`". $mlObject. "`.`langId` = ". $this->langId;
			$this->objectCollection->where->$mlObject .= " OR `". $mlObject. "`.`langId` IS NULL)";
		}

		unset($sql);
	}

	public function getGridData(){
		$sql = new database();

		$this->dataCollection->dataGrid = new stdClass();
		$data = & $this->dataCollection->dataGrid;
		$data->hasWeight = $this->hasWeight;
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
		if((int)$this->objectCollection->global->isTree == 1){
			$this->maxTreeLevel = 0;
		}
		$this->collectData($this->dataCollection->dataGrid->row);
		$this->dataCollection->dataGrid->maxTreeLevel = $this->maxTreeLevel;
		unset($sql);
	}

	public function getRecordData($recordId){
		$sql = new database();
		$this->recordView = true;
		$this->dataCollection->recordView = new stdClass();
		$this->dataCollection->recordView->header = array();
		$this->dataCollection->recordView->header[0] = new stdClass();
		$this->dataCollection->recordView->header[0]->name = $this->kwd("languageIndependentFields");
		$this->dataCollection->recordView->header[0]->langId = 0;
		$this->dataCollection->recordView->header[0]->cell = array();
		$sql->query("
			SELECT
				`lang`.`langId`
				, `lang`.`name`
			FROM
				`lang`
			ORDER BY
				`lang`.`default` DESC
		");
		$lang = array();
		if($sql->num_rows() > 0){
			do{
				$lang[$sql->langId] = $sql->name;
				$this->dataCollection->recordView->header[$sql->langId] = new stdClass();
				$this->dataCollection->recordView->header[$sql->langId]->name = $sql->name;
				$this->dataCollection->recordView->header[$sql->langId]->langId = $sql->langId;
				$this->dataCollection->recordView->header[$sql->langId]->cell = array();
			}while($sql->next());
		}

		$data = new stdClass();
		$data->recordId = $recordId;
		//TREE
		if((int)$this->objectCollection->global->isTree == 1){
			$cell = & $this->dataCollection->recordView->header[0]->cell[];
			$cell = new stdClass();
			$recursiveField = $this->objectCollection->global->recursiveField;
			$cell->field = $recursiveField;
			$cell->name = $this->kwd(preg_replace('/Id$/', '', $recursiveField));
			$cell->type = 'SELECT';
			$sql->query("
						SELECT
							`". $this->name. "`.`". $this->objectCollection->global->recursiveShowField. "` as `value`
						FROM
							`". $this->name. "`
						WHERE
							`". $this->name. "`.`". $this->name. "Id` = (
								SELECT
									`". $this->name. "` .`". $this->objectCollection->global->recursiveField. "`
								FROM
									`". $this->name. "`
								WHERE
									`". $this->name. "`.`". $this->name. "Id` = ". (int)$recordId. "
							)
					");
			$cell->data = $sql->value;
			$cell->collectData = new stdClass();
			$cell->collectData->table = $this->name;
			$cell->collectData->fieldValue = $this->objectCollection->global->recursiveShowField;
			$cell->collectData->fieldId = $this->name. "Id";
			$cell->collectData->recurseBy = $this->objectCollection->global->recursiveField;
		}

		foreach ($this->objectCollection->fields as $field => $property){
			$data->name = $field;
			$data->property = $property;
			if($property->multilanguage){
				foreach ($lang as $langId => $langName){
					$data->langId = $langId;
					$this->getFieldValue($data, $this->dataCollection->recordView->header[$langId]->cell[], $langId);
				}
			}else{
				$data->langId = 0;
				$this->getFieldValue($data, $this->dataCollection->recordView->header[0]->cell[], 0);
			}
		}

		unset($sql);
	}

	private function getFieldValue($field, &$collector, $langId){
		$sql = new database();
		$collector = new stdClass();
		if($field->property->relation){
			$collector->name = $this->kwd($field->property->relateTable);
		}else{
			$collector->name = $this->kwd($field->name);
		}
		$sysTypeId = "sys.typeId";
		$sql->query("
			SELECT
				`sys.type`.`fieldType` as `type`
			FROM
				`sys.type`
			WHERE
				`sys.type`.`sys.typeId` = ". (int)$field->property->$sysTypeId. "
		");
		$collector->type = $sql->type;
		$collector->field = $field->name;

		if(!$field->property->primary){
			if($field->langId > 0){
				$table = $this->name. TABLE_ML_SUFFIX;
				$where = " AND `$table`.`langId` = ". $field->langId;
			}else{
				$table = $this->name;
				$where = "";
			}
			$sql->query("
				SELECT
					`". $table. "`.`". $field->name. "` as `value`
				FROM
					`". $table. "`
				WHERE
					`". $table. "`.`". $this->name. "Id` = ". (int)$field->recordId. "
					". $where. "
			");
			if($field->property->relation){
				$sql->query("
					SELECT
						`". $field->property->relateTable. "`.`". $field->property->relateField. "` as `value`
					FROM
						`". $field->property->relateTable. "`
					WHERE
						`". $field->property->relateTable. "`.`". $field->property->relateTable. "Id` = ". (int)$sql->value. "
				");
				$collector->data = $sql->value;
				$collector->collectData = new stdClass();
				$collector->collectData->table = $field->property->relateTable;
				$collector->collectData->fieldValue = $field->property->relateField;
				$collector->collectData->fieldId = $field->property->relateTable. "Id";
			}else{
				$collector->data = $sql->value;
			}
		}else{
			$collector->data = $field->recordId;
		}

		if($field->langId > 0){
			$collector->langId = $field->langId;
		}

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
				$row->current = $sql->id;
				$row->parent = $parentId;
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
					if($level > $this->maxTreeLevel){
						$this->maxTreeLevel = $level;
					}
					$this->collectData($collector, $sql->id, ($level + 1));
				}
			}while($sql->next());
		}
		unset($sql);
	}

	public function xGetSelectData($arg, &$json){
		$json->data = array();
		$data = json_decode($arg['arg']);

		$this->name = $data->table;
		$this->objectCollection = new stdClass();
		$this->objectCollection->global = new stdClass();
		if(isset($data->recurseBy)){
			$this->objectCollection->global->isTree = 1;
			$this->objectCollection->global->recursiveField = $data->recurseBy;
		}else{
			$this->objectCollection->global->isTree = 0;
		}
		$this->objectCollection->fields = new stdClass();
		$f1 = $data->fieldValue;
		$f2 = $data->fieldId;

		$this->objectCollection->fields->$f1 = new stdClass();
		$this->objectCollection->fields->$f1->showInGrid = 1;
		$this->objectCollection->fields->$f1->field = $f1;
		$this->objectCollection->fields->$f1->isHeader = 0;
		$this->objectCollection->fields->$f1->primary = false;
		$this->objectCollection->fields->$f1->relation = false;

		$this->objectCollection->fields->$f2 = new stdClass();
		$this->objectCollection->fields->$f2->showInGrid = 1;
		$this->objectCollection->fields->$f2->field = $f2;
		$this->objectCollection->fields->$f2->isHeader = 0;
		$this->objectCollection->fields->$f2->primary = true;
		$this->objectCollection->fields->$f2->relation = false;

		$this->objectCollection->select = new stdClass();
		$this->objectCollection->select->$f1 = "`".$data->table. "`.`". $f1. "`";
		$this->objectCollection->select->$f2 = "`".$data->table. "`.`". $f2. "` as `id`";

		$this->collectData($json->data);
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
		if(isset($this->objectCollection->join)){
			foreach ($this->objectCollection->join as $key => $value){
				$query .= $value;
			}
		}
		$query .= " WHERE";
		if((int)$this->objectCollection->global->isTree == 1){
			$query .= " `". $this->name. "`.`". $this->objectCollection->global->recursiveField. "` = ". (int)$parentId;
		}else{
			$query .= " 1=1";
		}
		if(isset($this->objectCollection->where)){
			foreach ($this->objectCollection->where as $key => $value){
				$query .= $value;
			}
		}
		if(isset($this->objectCollection->global->order) && strlen($this->objectCollection->global->order) > 0){
			$query .= " ORDER BY ". $this->objectCollection->global->order;
		}

		return $query;
	}

	private function getObjectFields($object, $multilanguage = false){
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
			ORDER BY `sys.dependecies`.`weight` ASC
		");
		if($sql->num_rows() > 0){
			do{
				$field = $sql->field;
				$sql->row->primary = false;
				$sql->row->relation = false;
				$sql->row->multilanguage = $multilanguage;
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

}

?>