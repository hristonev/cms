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

		$json->deleteKwd = $this->kwd("delete");
		$json->alertDelete = $this->kwd("alertDelete");
		$json->positive = $this->kwd("yes");
		$json->negative = $this->kwd("no");

	}

	public function xDeleteRecord($arg, &$json){
		$table = preg_replace('/_'. $arg['id']. '/', '', $arg['object']);
		$sql = new database();
		$execute = array();
		$sql->query("
			SELECT
				`sys.dynamic`.`tableName`
				, `sys.dynamic`.`isMultiLanguage`
			FROM
				`sys.dynamic`
			WHERE
				`sys.dynamic`.`tableName` LIKE '". $table. "'
		");
		if((int)$sql->isMultiLanguage == 1){
			$execute[] = $sql->prepare("
				DELETE FROM `". $table. TABLE_ML_SUFFIX. "` WHERE `". $table. TABLE_ML_SUFFIX. "`.`". $table. "Id` = ". (int)$arg['id']. "
			");
		}
		$execute[] = $sql->prepare("
			DELETE FROM `". $table. "` WHERE `". $table. "`.`". $table. "Id` = ". (int)$arg['id']. "
		");
		foreach ($execute as $key => $value){
			$value->execute();
			$value->close();
		}
		$json->recordId = (int)$arg['id'];
		unset($sql);
	}

	public function xchangeWeight($arg, &$json){
		$table = $arg['table'];
		$sourceId = (int)$arg['source'];
		$targetId = (int)$arg['target'];
		$sql = new database();

		$sql->query("
			SELECT
				`sys.dynamic`.`tableName`
				, `sys.dynamic`.`weightField`
				, `sys.dynamic`.`isTree`
				, `sys.dynamic`.`recursiveField`
			FROM
				`sys.dynamic`
			WHERE
				`sys.dynamic`.`tableName` LIKE '". $table. "'
		");
		if($sql->tableName == $table){
			$isTree = ((int)$sql->isTree == 1) ? true : false;
			$recursiveFld = $sql->recursiveField;
			$weightFld = $sql->weightField;

			if($isTree){
				$select = ", `". $table. "`.`". $recursiveFld. "` as `parentId`";
			}else{
				$select = "";
			}

			$sql->query("
				SELECT
					`". $table. "`.`". $weightFld. "` as `weight`
					". $select. "
				FROM
					`". $table. "`
				WHERE
					`". $table. "`.`". $table. "Id` = ". $targetId. "
			");
			$target = new stdClass();
			$target->weight = (int)$sql->weight;
			$target->parentId = (int)$sql->parentId;

			$sql->query("
				SELECT
					`". $table. "`.`". $weightFld. "` as `weight`
					". $select. "
				FROM
					`". $table. "`
				WHERE
					`". $table. "`.`". $table. "Id` = ". $sourceId. "
			");
			$source = new stdClass();
			$source->weight = (int)$sql->weight;
			$source->parentId = (int)$sql->parentId;

			if($target->parentId == $source->parentId){
				$newWeight = $target->weight;
				if($target->weight > $source->weight){
					$upd = "- 1";
					$cond1 = "<=";
					$cond2 = ">";
				}else{
					$upd = "+ 1";
					$cond1 = ">=";
					$cond2 = "<";
				}
				$query = "
					UPDATE
						`". $table. "`
					SET
						`". $table. "`.`". $weightFld. "` = `". $table. "`.`". $weightFld. "` ". $upd. "
					WHERE
						`". $table. "`.`". $weightFld. "` ". $cond1. " ". $target->weight. "
					AND
						`". $table. "`.`". $weightFld. "` ". $cond2. " ". $source->weight. "
				";
				if($isTree){
					$query .= " AND `". $table. "`.`". $recursiveFld. "` = ". $target->parentId. "";
				}
				$sql->exec($query);
				$query = "
					UPDATE
						`". $table. "`
					SET
						`". $table. "`.`". $weightFld. "` = ". $newWeight. "
					WHERE
						`". $table. "`.`". $table. "Id` = ". $sourceId. "
				";
				$sql->exec($query);
			}
		}

		unset($sql);
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
				$data['recordId'] = $sql->insert_id;
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

		//update weight if parent is changed
		$sql->query("
			SELECT
				`sys.dynamic`.`tableName`
				, `sys.dynamic`.`weightField`
				, `sys.dynamic`.`isTree`
				, `sys.dynamic`.`recursiveField`
			FROM
				`sys.dynamic`
			WHERE
				`sys.dynamic`.`tableName` LIKE '". $data['tableName']. "'
		");
		if((int)$sql->isTree == 1 && $data['field'] == $sql->recursiveField && $sql->weightField != ""){
			$weightFld = $sql->weightField;
			$recursiveFld = $sql->recursiveField;
			$sql->query("
				SELECT
					`". $data['tableName']. "`.`". $weightFld. "` as `weight`
				FROM
					`". $data['tableName']. "`
				WHERE
					`". $data['tableName']. "`.`". $recursiveFld. "` = ". (int)$data['value']. "
				ORDER BY
					`". $data['tableName']. "`.`". $weightFld. "` DESC
				LIMIT 1
			");

			$sql->exec("
				UPDATE
					`". $data['tableName']. "`
				SET
					`". $data['tableName']. "`.`". $weightFld. "` = ". ((int)$sql->weight + 1). "
				WHERE
					`". $data['tableName']. "`.`". $data['tableName']. "Id` = ". $data['recordId']. "
			");
		}

		unset($sql);
	}

}

?>