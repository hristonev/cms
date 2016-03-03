<?php 

class cmsView extends user
{
	private $fields = array();
	private $ml = false;
	
	public function __construct(){
		parent::__construct();

		$this->childClass = & $this;
	}

	public function xGetGrid($arg, &$json){
		
// 		$xml = new xml();
		$table = preg_replace('/\s+/', '', $arg['object']);
		$sql = new database();
		$sql1 = new database();
		$sql->query("
			SELECT 
				COUNT(`". $table. "`.`". $table. "Id`) as total 
			FROM 
				`". $table. "`
		");
		$json->totalRecords = new stdClass();
		$json->totalRecords->name = $this->kwd('totalRecords');
		$json->totalRecords->value = $sql->total;
// 		$xml->addNode('totalRecords');
// 		$xml->setAttribute('name', $this->kwd('totalRecords'));
// 		$xml->setAttribute('value', $sql->total);
		
		$json->newRecord = new stdClass();
		$json->newRecord->value = $this->kwd('newRecord');
// 		$xml->addNode('newRecord');
// 		$xml->setAttribute('value', $this->kwd('newRecord'));
		
		$json->saveRecord = new stdClass();
		$json->saveRecord->value = $this->kwd('saveRecord');
// 		$xml->addNode('saveRecord');
// 		$xml->setAttribute('value', $this->kwd('saveRecord'));
		
// 		$grid = $row = $xml->addNode('dataGrid');
// 		$total = 51;
// 		for($i = 1; $i < $total; $i++){
// 			$row = $xml->addNode('row', '', false, $grid);
// 			if($i == 1){
// 				$xml->setAttribute('type', 'header');
// 			}
// 			for($x = 1; $x < $total; $x++){
// 				$xml->addNode('cell', ($i * $x), false, $row);
// 				if($x == 1)
// 					$xml->setAttribute('type', 'header');
// 			}
// 		}
		
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
// 			$grid = $xml->addNode('dataGrid');
// 			$row = $xml->addNode('row', '', false, $grid);
// 			$xml->setAttribute('type', 'header');
			$row->cell = array();
			do{
				if(isset($realFieldSet[$sql->field])){
					$cell = &$row->cell[];
					$cell = new stdClass();
// 					$cell = $xml->addNode('cell', '', false, $row);
					if($primary == $sql->field){
						$cell->primary = true;
// 						$xml->setAttribute('primary', 1);
					}
					if($sql->field != $table. 'Id'){
						$cell->name = $this->kwd($sql->field);
// 						$xml->setAttribute('name', $this->kwd($sql->field));
					}else{
						$cell->name = $this->kwd('id');
// 						$xml->setAttribute('name', $this->kwd('id'));
					}
					$cell->width = $sql->columnWidth;
// 					$xml->setAttribute('width', $sql->columnWidth);
					if((int)$sql->isHeader == 1){
						$cell->type = 'header';
// 						$xml->setAttribute('type', 'header');
						$headerSet[$sql->field] = true;
					}
					$fieldSet[$sql->field] = "`". $sql->table. "`.`". $sql->field. "`";
				}
			}while ($sql->next());
		}
		$query = "
			SELECT 
				" . implode(",", $fieldSet). " 
			FROM 
				`". $table. "`
		";
		if($this->ml){
			$query .= " LEFT JOIN `". $table. TABLE_ML_SUFFIX. "` ON `". $table. TABLE_ML_SUFFIX. "`.`". $table. "Id` = `". $table. "`.`". $table. "Id`";
		}
		$sql->query($query);
		if($sql->num_rows() > 0){
			do{
				$row = &$json->dataGrid->row[];
				$row = new stdClass();
// 				$row = $xml->addNode('row', '', false, $grid);
				foreach ($fieldSet as $key => $value){
					$cell = &$row->cell[];
					$cell = new stdClass();
					$cell->name = $sql->$key;
// 					$xml->addNode('cell', '', false, $row);
// 					$xml->setAttribute('name', $sql->$key);
					if(isset($headerSet[$key])){
						$cell->type = 'header';
// 						$xml->setAttribute('type', 'header');
					}
				}
			}while ($sql->next());
		}
		unset($sql);
		unset($sql1);
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
// 		$view = $xml->addNode('recordView');
		$recordId = (int)$arg['recordId'];
		
		//language unrelated fields
		$json->recordView->header = array();
		$header = &$json->recordView->header[];
		$header = new stdClass();
		$header->name = $this->kwd("languageIndependentFields");
// 		$header = $xml->addNode('header', '', false, $view);
// 		$xml->setAttribute('name', $this->kwd("languageIndependentFields"));
		$header->cell = array();
		if($sql->num_rows() > 0){
			do{
				$cell = &$header->cell[];
				$cell = new stdClass();
				$cell->name = $sql->Field;
// 				$xml->addNode('cell', $sql->Field, false, $header);
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
// 					$xml->setAttribute('type', $sql1->type);
				}else{
					$cell->type = 'DISABLE';
// 					$xml->setAttribute('type', "DISABLE");
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
// 					$xml->addNode('data', $sql2->data, false, $header);
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
					//$xml->addNode('cell', $sql->Field, false, $header);
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
// 					$header = $xml->addNode('header', '', false, $view);
// 					$xml->setAttribute('name', $sql->name);
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
// 						$xml->addNode('cell', $field, false, $header);
						if((int)$sql1->id > 0){
							$cell->type = $sql1->type;
// 							$xml->setAttribute('type', $sql1->type);
						}else{
							$cell->type = 'DISABLE';
// 							$xml->setAttribute('type', "DISABLE");
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
// 							$xml->addNode('data', $sql2->data, false, $header);
						}
					}
				}while($sql->next());
			}
		}
		
		unset($sql2);
		unset($sql1);
		unset($sql);
	}
	
	public function xSaveRecord($arg, &$xml){
		$sql = new database();
// 		$data = json_decode($arg['data'], true);
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