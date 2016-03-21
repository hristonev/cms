<?php

class cmsView extends user
{
	use mapTool;

	private $fields = array();
	private $ml = false;

	public function __construct(){
		parent::__construct();

		$this->childClass = & $this;
	}

	public function xGetGrid($arg, &$json){

		$table = preg_replace('/\s+/', '', $arg['object']);
		$sql = new database();
		$sql1 = new database();

		$sql->query("
			SHOW TABLES LIKE '". $table. TABLE_ML_SUFFIX. "'
		");
		if($sql->num_rows() > 0){
			$this->ml = true;
		}

		$sql->query("
			SELECT
				COUNT(`". $table. "`.`". $table. "Id`) as total
			FROM
				`". $table. "`
		");
		$json->totalRecords = new stdClass();
		$json->totalRecords->name = $this->kwd('totalRecords');
		$json->totalRecords->value = $sql->total;

		$json->newRecord = new stdClass();
		$json->newRecord->value = $this->kwd('newRecord');

		$json->saveRecord = new stdClass();
		$json->saveRecord->value = $this->kwd('saveRecord');
		//get header for table
		$primary = "";
		$sql->query("SHOW COLUMNS FROM `". $table. "`");
		$realFieldSet = array();
		if($sql->num_rows() > 0){
			do{
				if($sql->Key == "PRI"){
					$primary = $sql->Field;
				}
				$realFieldSet[$sql->Field] = '';
			}while ($sql->next());
		}
		if($this->ml){
			$sql->query("SHOW COLUMNS FROM `". $table. TABLE_ML_SUFFIX. "`");
			if($sql->num_rows() > 0){
				do{

					$realFieldSet[$sql->Field] = '';
				}while($sql->next());
			}
		}
		$sql->query("
			SELECT
				`sys.dependecies`.`table` as `table`
				, `sys.dependecies`.`field` as `field`
				, `sys.dependecies`.`relateTable` as `relateTable`
				, `sys.dependecies`.`relateField` as `relateField`
				, `sys.dependecies`.`showInView` as `showInView`
				, `sys.dependecies`.`showInGrid` as `showInGrid`
				, `sys.dependecies`.`isHeader` as `isHeader`
				, `sys.dependecies`.`columnWidth` as `columnWidth`
				, `sys.dependecies`.`weight` as `weight`
				, `sys.type`.`code` as `type`
				, `sys.type`.`phpExpression` as `phpExp`
				, `sys.type`.`sqlExpression` as `sqlExp`
				, `sys.type`.`sqlResultCode` as `sqlResultCode`
				, `sys.type`.`sqlResultValue` as `sqlResultValue`
				, `sys.type`.`jsExpression` as `jsExp`
			FROM
				`sys.dependecies`
			LEFT JOIN `sys.type` ON `sys.type`.`sys.typeId` = `sys.dependecies`.`sys.typeId`
			WHERE
				`sys.dependecies`.`table` = '". $table. "'
			OR
				`sys.dependecies`.`table` = '". $table. TABLE_ML_SUFFIX. "'
			ORDER BY
				`sys.dependecies`.`table` ASC
				, `sys.dependecies`.`weight` ASC
		");
		$fieldSet = array();
		$headerSet = array();
		if($sql->num_rows() > 0){
			$json->dataGrid = new stdClass();
			$json->dataGrid->row = array();
			$row = &$json->dataGrid->row[];
			$row = new stdClass();
			$row->type = 'header';
			$row->cell = array();
			do{
				if(isset($realFieldSet[$sql->field]) && (int)$sql->showInGrid == 1){
					$cell = &$row->cell[];
					$cell = new stdClass();
					if($primary == $sql->field){
						$cell->primary = true;
					}
					if($sql->field != $table. 'Id'){
						$cell->name = $this->kwd($sql->field);
					}else{
						$cell->name = $this->kwd('id');
					}
					$cell->code = $sql->field;
					$cell->width = $sql->columnWidth;
					if((int)$sql->isHeader == 1){
						$cell->type = 'header';
						$headerSet[$sql->field] = true;
					}
					$fieldSet[$sql->field] = "`". $sql->table. "`.`". $sql->field. "`";
				}
			}while ($sql->next());
		}

		$data = new stdClass();
		$data->fieldSet = $fieldSet;
		$data->table = $table;
		$data->headerSet = $headerSet;
		$data->objProperty = $this->getObjectProperties($table);
		$data->json = $json;
		$this->getDataRows($data, $this->getRootId($table));

		unset($sql);
		unset($sql1);
	}

	//RECURSIVE IF OBJECT IS TREE
	private function getDataRows(&$data, $parentId){
		$sql = new database();
		$fieldSet = $data->fieldSet;
		if(!is_null($parentId) && (int)$data->objProperty->isTree == 1){
			$parentField = $data->objProperty->recursiveField;
			$primary = $data->table. "Id";
			$fieldSet[$parentField] = "`". $data->table. "`.`". $parentField. "`";
		}
		$query = "
			SELECT
				" . implode(",", $fieldSet). "
			FROM
				`". $data->table. "`
		";
		if($this->ml){
			$query .= " LEFT JOIN `". $data->table. TABLE_ML_SUFFIX. "` ON `". $data->table. TABLE_ML_SUFFIX. "`.`". $data->table. "Id` = `". $data->table. "`.`". $data->table. "Id`";
			$query .= " WHERE `". $data->table. TABLE_ML_SUFFIX. "`.`langId` = ". $this->langId;
		}

		if(!is_null($parentId) && (int)$data->objProperty->isTree == 1){
			if($this->ml){
				$query .= " AND ";
			}else{
				$query .= " WHERE ";
			}
			$query .= "`". $data->table. "`.`". $parentField. "` = ". (int)$parentId;
		}

		if(!is_null($data->objProperty)){
			$query .= " ORDER BY ". $data->objProperty->order;
		}
		$sql->query($query);
		if($sql->num_rows() > 0){
			do{
				$row = &$data->json->dataGrid->row[];
				$row = new stdClass();
				foreach ($data->fieldSet as $key => $value){
					$cell = &$row->cell[];
					$cell = new stdClass();
					$cell->name = $sql->$key;
					if(isset($data->headerSet[$key])){
						$cell->type = 'header';
					}
				}
				if(!is_null($parentId) && (int)$data->objProperty->isTree == 1 && (int)$sql->$primary > 0){
					$this->getDataRows($data, $sql->$primary);
				}
			}while ($sql->next());
		}
		unset($sql);
	}

	public function xGetRecordView($arg, &$json){
		$xml = new xml();
		$table = preg_replace('/\s+/', '', $arg['object']);
		$data = array();
		$sql = new database();
		$sql1 = new database();
		$sql2 = new database();

		$sql->query("
			SHOW TABLES LIKE '". $table. TABLE_ML_SUFFIX. "'
		");
		if($sql->num_rows() > 0){
			$this->ml = true;
		}

		$sql->query("SHOW COLUMNS FROM `". $table. "` WHERE `Key` = 'PRI'");
		$primary = $sql->Field;

		$sql->query("SHOW COLUMNS FROM `". $table. "`");
		$json->recordView = new stdClass();
		$recordId = (int)$arg['recordId'];

		//language unrelated fields
		$json->recordView->header = array();
		$header = &$json->recordView->header[];
		$header = new stdClass();
		$header->name = $this->kwd("languageIndependentFields");
		$header->langId = 0;
		$header->cell = array();
		if($sql->num_rows() > 0){
			do{
				$cell = &$header->cell[];
				$cell = new stdClass();
				$cell->name = $sql->Field;
				$sql1->query("
					SELECT
						`sys.type`.`sys.typeId` as `id`
						, `sys.type`.`fieldType` as `type`
					FROM
						`sys.dependecies`
					JOIN `sys.type` ON `sys.type`.`sys.typeId` = `sys.dependecies`.`sys.typeId`
					WHERE
						`sys.dependecies`.`table` = '". $table. "'
					AND
						`sys.dependecies`.`field` = '". $sql->Field. "'
				");
				if((int)$sql1->id > 0){
					$cell->type = $sql1->type;
				}else{
					$cell->type = 'DISABLE';
				}
				//get data
				if($recordId > 0){
					$sql2->query("
						SELECT
							`". $table. "`.`". $sql->Field. "` as data
						FROM
							`". $table. "`
						WHERE
							`". $table. "`.`". $primary. "` = ". $recordId. "
					");
					$cell->data = $sql2->data;
				}
			}while ($sql->next());
		}

		//language related fields
		if($this->ml){
			$fields = array();
			$sql->query("SHOW COLUMNS FROM `". $table. TABLE_ML_SUFFIX. "`");
			if($sql->num_rows() > 0){
				do{
					$fields[$sql->Field] = array();
				}while($sql->next());
			}

			$sql->query("
				SELECT
					`lang`.`langId`
					, `lang`.`name`
				FROM
					`lang`
			");
			if($sql->num_rows() > 0){
				do{
					$header = &$json->recordView->header[];
					$header = new stdClass();
					$header->name = $sql->name;
					$header->langId = $sql->langId;
					foreach ($fields as $field => $value){
						$sql1->query("
							SELECT
								`sys.type`.`sys.typeId` as `id`
								, `sys.type`.`fieldType` as `type`
							FROM
								`sys.dependecies`
							JOIN `sys.type` ON `sys.type`.`sys.typeId` = `sys.dependecies`.`sys.typeId`
							WHERE
								`sys.dependecies`.`table` = '". $table. TABLE_ML_SUFFIX. "'
							AND
								`sys.dependecies`.`field` = '". $field. "'
						");
						$cell = &$header->cell[];
						$cell = new stdClass();
						$cell->name = $field;
						$cell->langId = $sql->langId;
						if((int)$sql1->id > 0){
							$cell->type = $sql1->type;
						}else{
							$cell->type = 'DISABLE';
						}
						//get data
						if($recordId > 0){
							$sql2->query("
								SELECT
									`". $table. TABLE_ML_SUFFIX. "`.`". $field. "` as data
								FROM
									`". $table. TABLE_ML_SUFFIX. "`
								WHERE
									`". $table. TABLE_ML_SUFFIX. "`.`". $primary. "` = ". $recordId. "
								AND
									`". $table. TABLE_ML_SUFFIX. "`.`langId` = ". $sql->langId. "
							");
							$cell->data = $sql2->data;
						}
					}
				}while($sql->next());
			}
		}

		unset($sql2);
		unset($sql1);
		unset($sql);
	}

	public function xSaveRecord($arg, &$json){
		$sql = new database();
		$dataStr = preg_replace('/\r\n|\r|\n/', '', $arg['data']);
		file_put_contents("/usr/local/www/htse/www/json", $dataStr);
		$data = json_decode($dataStr, true);
		$this->checkJSON();

		$table = $data['object'];
		$tableML = $table. TABLE_ML_SUFFIX;
		$idFld = $table. "Id";
		$mlIdFld = $tableML. "Id";
		$langIdFld = "langId";

		//non ml table
		$query = "";
		$insert = false;
		$execute = false;
		$langId = 0;
		if((int)$data[0][$idFld] > 0){
			$query .= "UPDATE ". $table;
		}else{
			$query .= "INSERT INTO ". $table;
			$insert = true;
		}
		$query .= " SET ";
		$delimiter = "";
		foreach ($data[0] as $key => $value){
			if($key != $idFld && strlen($value) > 0){
				$query .= $delimiter. "`". $key. "` = '". $sql->real_escape_string($value). "'";
				$delimiter = ", ";
				$execute = true;
			}
		}
		if(!$insert){
			$query .= " WHERE `". $idFld. "` = ". (int)$data[0][$idFld];
		}
		if($execute){
			$sql->exec($query);
			$json->$langId = new stdClass();
			$json->$langId->$idFld = $sql->insert_id;
		}

		//ml table
		if(count($data) > 1){
			foreach ($data as $langId => $dataSet){
				$query = "";
				$insert = false;
				$execute = false;
				if((int)$langId > 0){
					if((int)$data[$langId][$mlIdFld] > 0){
						$query .= "UPDATE ". $tableML;
					}else{
						$query .= "INSERT INTO ". $tableML;
						$insert = true;
					}
					$query .= " SET ";
					$query .= $idFld . " = ". (int)$data[0][$idFld];
					$query .= ", ". $langIdFld . " = ". (int)$langId;
					$delimiter = ", ";
					foreach ($data[$langId] as $key => $value){
						if($key != $idFld && $key != $langIdFld && $key != $mlIdFld && strlen($value) > 0){
							$query .= $delimiter. "`". $key. "` = '". $sql->real_escape_string($value). "'";
							$execute = true;
						}
					}
					if(!$insert){
						$query .= " WHERE `". $mlIdFld. "` = ". (int)$data[$langId][$mlIdFld];
					}
				}
				if($execute){
					$sql->exec($query);
					$json->$langId = new stdClass();
					$json->$langId->$mlIdFld = $sql->insert_id;
				}
			}
		}
// 		dump($data);
// 		dump(json_decode($arg['data'], false, 512, JSON_BIGINT_AS_STRING));
// 		$query = "INSERT INTO `". $arg['object']. "` SET ";
// 		$delimiter = "";
// 		foreach ($data as $key => $value){
// 			$query .= $delimiter. "`". $key. "` = '". $value. "' ";
// 			$delimiter = ",";
// 		}
// 		dump($query);
// 		$sql->exec($query);
		unset($sql);
	}

}

?>