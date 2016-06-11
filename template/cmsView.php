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

		//get grid
		$object = new object($table);
		$object->setDataCollectObject($json);
		$object->initObject();

		//global controls for grid
		$sql = new database();
		$sql->query("
			SELECT
				COUNT(`". $table. "`.`". $table. "Id`) as total
			FROM
				`". $table. "`
		");

		if(!$object->isCustom()){
			$object->getGridData();
		}else{
			$json->customTemplate = $object->getTemplate();
		}

		$json->totalRecords = new stdClass();
		$json->totalRecords->name = $this->kwd('totalRecords');
		$json->totalRecords->value = $sql->total;

		$json->newRecord = new stdClass();
		$json->newRecord->value = $this->kwd('newRecord');

		$json->saveRecord = new stdClass();
		$json->saveRecord->value = $this->kwd('saveRecord');

		unset($sql);
	}

	public function xGetRecordView($arg, &$json){

		$table = preg_replace('/\s+/', '', $arg['object']);
		$recordId = (int)$arg['recordId'];

		//get record
		$object = new object($table);
		$object->setDataCollectObject($json);
		$object->initObject();
		$object->getRecordData($recordId);
	}

	public function xSaveRecord($arg, &$json){
		$sql = new database();
		$dataStr = preg_replace('/\r\n|\r|\n/', '', $arg['data']);
		$data = json_decode($dataStr, true);
		$primaryKey = $data['tableName']. 'Id';
		$action = "";
		$object = "";
		$value = "";
		$condition = "";
		$newRecord = false;
		$newMLRecord = false;
		if((int)$data['recordId'] > 0){// have main record
			if((int)$data['langId'] > 0){// ML
				$sql->query("
					SELECT
						`". $data['tableName']. TABLE_ML_SUFFIX. "`.`". $data['tableName']. TABLE_ML_SUFFIX. "Id` as `id`
					FROM
						`". $data['tableName']. TABLE_ML_SUFFIX. "`
					WHERE
						`". $data['tableName']. TABLE_ML_SUFFIX. "`.`". $data['tableName']. "Id` = ". (int)$data['recordId']. "
					AND
						`". $data['tableName']. TABLE_ML_SUFFIX. "`.`langId` = ".(int)$data['langId']. "
				");
				if((int)$sql->id > 0){
					$action .= "UPDATE `". $data['tableName']. TABLE_ML_SUFFIX. "` SET ";
					$condition .= "WHERE `". $data['tableName']. TABLE_ML_SUFFIX. "`.`". $data['tableName']. TABLE_ML_SUFFIX. "Id` = ". (int)$sql->id;
				}else{
					$action .= "INSERT INTO `". $data['tableName']. TABLE_ML_SUFFIX. "` SET ";
					$value .= "`". $data['tableName']. TABLE_ML_SUFFIX. "`.`". $data['tableName']. "Id` = ". (int)$data['recordId']. ", ";
					$value .= "`". $data['tableName']. TABLE_ML_SUFFIX. "`.`langId` = ". (int)$data['langId']. ", ";
					$newMLRecord = true;
				}
				$value .= "`". $data['tableName']. TABLE_ML_SUFFIX. "`.`". $data['field']. "` = '". $sql->real_escape_string($data['value']). "' ";
			}else{
				$action .= "UPDATE `". $data['tableName']. "` SET ";
				$value .= "`". $data['tableName']. "`.`". $data['field']. "` = '". $sql->real_escape_string($data['value']). "' ";
				$condition .= "WHERE `". $data['tableName']. "`.`". $data['tableName']. "Id` = ". (int)$data['recordId'];
			}
		}else{// insert new main table record
			$newRecord = true;
			if((int)$data['langId'] > 0){// ML
				$newMLRecord = true;
				$sql->exec("INSERT INTO `". $data['tableName']. "` () VALUES()");
				$json->recordId = $sql->insert_id;
				$action .= "INSERT INTO `". $data['tableName']. TABLE_ML_SUFFIX. "` SET ";
				$value .= "`". $data['tableName']. TABLE_ML_SUFFIX. "`.`". $data['tableName']. "Id` = ". (int)$sql->insert_id. ", ";
				$value .= "`". $data['tableName']. TABLE_ML_SUFFIX. "`.`langId` = ". (int)$data['langId']. ", ";
				$value .= "`". $data['tableName']. TABLE_ML_SUFFIX. "`.`". $data['field']. "` = '". $sql->real_escape_string($data['value']). "' ";
			}else{
				$action .= "INSERT INTO `". $data['tableName']. "` SET ";
				$value .= "`". $data['tableName']. "`.`". $data['field']. "` = '". $sql->real_escape_string($data['value']). "' ";
			}
		}
		if($newRecord){
			$sql->exec($action. $object. $value. $condition);
			if($newMLRecord){
				$json->recordMLId = $sql->insert_id;
			}else{
				$json->recordId = $sql->insert_id;
			}
		}else{
			$sql->exec($action. $object. $value. $condition);
		}
// 		$json->query = $action. $object. $value. $condition;
// 		$dataStr = preg_replace('/\r\n|\r|\n/', '', $arg['data']);
// 		file_put_contents("/usr/local/www/htse/www/json", $dataStr);
// 		$data = json_decode($dataStr, true);
// 		$this->checkJSON();

// 		$table = $data['object'];
// 		$tableML = $table. TABLE_ML_SUFFIX;
// 		$idFld = $table. "Id";
// 		$mlIdFld = $tableML. "Id";
// 		$langIdFld = "langId";

// 		//non ml table
// 		$query = "";
// 		$insert = false;
// 		$execute = false;
// 		$langId = 0;
// 		if((int)$data[0][$idFld] > 0){
// 			$query .= "UPDATE ". $table;
// 		}else{
// 			$query .= "INSERT INTO ". $table;
// 			$insert = true;
// 		}
// 		$query .= " SET ";
// 		$delimiter = "";
// 		foreach ($data[0] as $key => $value){
// 			if($key != $idFld && strlen($value) > 0){
// 				$query .= $delimiter. "`". $key. "` = '". $sql->real_escape_string($value). "'";
// 				$delimiter = ", ";
// 				$execute = true;
// 			}
// 		}
// 		if(!$insert){
// 			$query .= " WHERE `". $idFld. "` = ". (int)$data[0][$idFld];
// 		}
// 		if($execute){
// 			$sql->exec($query);
// 			$json->$langId = new stdClass();
// 			$json->$langId->$idFld = $sql->insert_id;
// 		}

// 		//ml table
// 		if(count($data) > 1){
// 			foreach ($data as $langId => $dataSet){
// 				$query = "";
// 				$insert = false;
// 				$execute = false;
// 				if((int)$langId > 0){
// 					if((int)$data[$langId][$mlIdFld] > 0){
// 						$query .= "UPDATE ". $tableML;
// 					}else{
// 						$query .= "INSERT INTO ". $tableML;
// 						$insert = true;
// 					}
// 					$query .= " SET ";
// 					$query .= $idFld . " = ". (int)$data[0][$idFld];
// 					$query .= ", ". $langIdFld . " = ". (int)$langId;
// 					$delimiter = ", ";
// 					foreach ($data[$langId] as $key => $value){
// 						if($key != $idFld && $key != $langIdFld && $key != $mlIdFld && strlen($value) > 0){
// 							$query .= $delimiter. "`". $key. "` = '". $sql->real_escape_string($value). "'";
// 							$execute = true;
// 						}
// 					}
// 					if(!$insert){
// 						$query .= " WHERE `". $mlIdFld. "` = ". (int)$data[$langId][$mlIdFld];
// 					}
// 				}
// 				if($execute){
// 					$sql->exec($query);
// 					$json->$langId = new stdClass();
// 					$json->$langId->$mlIdFld = $sql->insert_id;
// 				}
// 			}
// 		}
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