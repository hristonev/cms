<?php

trait mapTool{
	protected function getObjectProperties($object){
		$sql = new database();
		$sql->query("
			SELECT
				*
			FROM
				`sys.dynamic`
			WHERE
				`sys.dynamic`.`tableName` = '". $object. "'
		");
		if($sql->num_rows() > 0){
			$result = $sql->row;
		}else{
			$result = NULL;
		}
		unset($sql);
		return $result;
	}

	protected function getRootId($object){
		$rootId = null;
		$sql = new database();
		$sql->query("
			SELECT
				*
			FROM
				`sys.dynamic`
			WHERE
				`sys.dynamic`.`tableName` = '". $object. "'
		");
		if((int)$sql->isTree == 1){
			$sql->query("
				SELECT
					`". $object. "`.`". $sql->recursiveField. "` as `id`
				FROM
					`". $object. "`
				ORDER BY
					`". $object. "`.`". $sql->recursiveField. "` ASC
				LIMIT 1
			");
			$rootId = $sql->id;
		}
		unset($sql);
		return $rootId;
	}

}

class siteMap
{
	use mapTool;

	private $nodes;
	private $langId;
	private $langNode;
	private $base;
	private $FSMode = 0755;

	public function __construct(){
		$this->nodes = new stdClass();
		$this->base = new base();
	}

	public function xCreateNode($arg, &$json){
		$cmsBaseDir = getcwd();
		chdir(SITEMAP_BASE);
		$json = new stdClass();
		$sql = new database();
		$sql->query("
			SELECT
				`sys.siteMap`.*
			FROM
				`sys.siteMap`
			WHERE
				`sys.siteMap`.`sys.siteMapId` = '". (int)$arg['id']. "'
		");
		if(!is_dir($sql->path)){
			mkdir($sql->path);
			chmod($sql->path, $this->FSMode);
		}
		$fp = fopen($sql->path. "/index.php", "w");
		fwrite($fp, "<?php
if(session_status() !== PHP_SESSION_ACTIVE){
	session_start();
}
global \$pathId;
\$pathId = ". (int)$arg['id']. ";
include('". getcwd(). "/index.php');
?>");
		fclose($fp);
		chmod($sql->path. "/index.php", $this->FSMode);
		chdir($cmsBaseDir);
		unset($sql);
	}

	public function xGetData($arg, &$json){
		$json = new stdClass();
		$sql = new database();
		$sql->query("
			SELECT
				`lang`.`langId`
				, `lang`.`shortName`
			FROM
				`lang`
		");
		$json->siteMap = array();
		if($sql->num_rows() > 0){
			do{
				$node = & $json->siteMap[];
				$node = new stdClass();
				$node->id = 0;
				$node->name = $sql->shortName;
				$node->langId = $sql->langId;
				$node->dynamicId = 0;
				$node->relateTable = '';
				$this->langId = $sql->langId;
				$this->langNode = $sql->shortName;
				$node->children = $this->getChildNodes();
			}while($sql->next());
		}
		$cmsBaseDir = getcwd();
		chdir(SITEMAP_BASE);
		$json->workDir = $this->base->kwd('attentionSiteMapWorkingDir'). getcwd();
		$json->makeDirectories = $this->base->kwd('SiteMapMakeDirectories');
		$sql = new database();
		$this->saveData('', $json->siteMap, $sql);
		chdir($cmsBaseDir);
		unset($sql);
	}

	//RECURSIVE METHOD
	private function saveData($path, &$json, &$sql){
		foreach ($json as $key => $value){
			if(property_exists($value, 'name')){
				$sql->query("
					SELECT
						`sys.siteMap`.`sys.siteMapId` as `id`
					FROM
						`sys.siteMap`
					WHERE
						`sys.siteMap`.`path` = '". $path. $value->name. "'
				");
				if((int)$sql->id <= 0 && strlen($value->name) > 0){
					$sql->exec("
						INSERT INTO
							`sys.siteMap`
						SET
							`sys.siteMap`.`path` = '". $path. $value->name. "'
							, `sys.siteMap`.`langId` = '". $value->langId. "'
							, `sys.siteMap`.`masterTableId` = '". $value->id. "'
							, `sys.siteMap`.`relateTableId` = '". $value->dynamicId. "'
							, `sys.siteMap`.`relateTableName` = '". $value->relateTable. "'
					");
					$value->sqlId = (int)$sql->insert_id;
				}else{
					$sql->exec("
						UPDATE
							`sys.siteMap`
						SET
							`sys.siteMap`.`path` = '". $path. $value->name. "'
							, `sys.siteMap`.`langId` = '". $value->langId. "'
							, `sys.siteMap`.`masterTableId` = '". $value->id. "'
							, `sys.siteMap`.`relateTableId` = '". $value->dynamicId. "'
							, `sys.siteMap`.`relateTableName` = '". $value->relateTable. "'
						WHERE
							`sys.siteMap`.`sys.siteMapId` = ". (int)$sql->id. "
					");
					$value->sqlId = (int)$sql->id;
				}
			}
			if(property_exists($value, 'children')){
				$newPath = $path;
				if(property_exists($value, 'name')){
					$newPath .= $value->name. '/';
				}
				$this->saveData($newPath, $value->children, $sql);
			}
		}
	}

	//RECURSIVE METHOD
	private function getChildNodes($table = SITEMAP_MASTER_TABLE, $parentId = 0, $dynamicId = 0){
		$sql = new database();
		$nodeCollection = array();

		$objProperty = $this->getObjectProperties($table);

		$query = "SELECT ";
		$query .= "`". $table. "`.`". $table. "Id` as `id`, ";
		$query .= "`". $table. "`.`showInPath`, ";
		if((int)$objProperty->isMultiLanguage == 1){//SELECT FROM ML TABLE
			$query .= "`". $table. TABLE_ML_SUFFIX. "`.`". $objProperty->siteMapField. "` as `nodeValue`";
		}else{//SELECT FROM MAIN TABLE
			$query .= "`". $table. "`.`". $objProperty->siteMapField. "` as `nodeValue`";
		}
		$query .= " FROM `". $table. "`";
		if((int)$objProperty->isMultiLanguage == 1){//JOIN ML TABLE
			$query .= " LEFT JOIN `". $table. TABLE_ML_SUFFIX. "` ON ";
			$query .= "`". $table. TABLE_ML_SUFFIX. "`.`". $table. "Id` = ";
			$query .= "`". $table. "`.`". $table. "Id`";
			$query .= " AND ";
			$query .= "`". $table. TABLE_ML_SUFFIX. "`.`langId` = ". $this->langId;
		}
		if((int)$objProperty->isTree == 1){//ADD WHERE CLAUSE PARENT FOR TREE
			$query .= " WHERE `". $table. "`.`". $objProperty->recursiveField. "` = ". (int)$parentId;
		}
		$sql->query($query);
		if($sql->num_rows() > 0){
			do{
				$node = & $nodeCollection[];
				$node = new stdClass();
				$node->langId = $this->langId;
				$node->dynamicId = 0;
				$node->relateTable = '';
				if($dynamicId > 0){
					$node->parentId = $dynamicId;
				}else{
					$node->parentId = $parentId;
				}
				if((int)$sql->showInPath == 1){
					$node->id = $sql->id;
					$node->name = $sql->nodeValue;
				}
				$this->getDynamicNodes($node, $objProperty->tableName, $sql->id);
			}while($sql->next());
		}

		unset($sql);

		return $nodeCollection;
	}

	private function getDynamicNodes(&$nodeCollection, $table, $id, $parentId = 0){
		$sql1 = new database();
		$sql2 = new database();
		$sql1->query("
			SELECT
				`sys.dynamic`.*
			FROM
				`". $table. "`
			JOIN `sys.dynamic` ON `sys.dynamic`.`mainTableId` = `". $table. "`.`". $table. "Id`
			WHERE
				`". $table. "`.`". $table. "Id` = ". (int)$id. "
		");
		if($sql1->num_rows() > 0){
			$primary = $sql1->tableName. "Id";
			$value = $sql1->siteMapField;
			$query = "SELECT ";
			$query .= "`". $sql1->tableName. "`.* ";
			$query .= ", `". $sql1->tableName. TABLE_ML_SUFFIX. "`.* ";
			$query .= "FROM ";
			$query .= "`". $sql1->tableName. "` ";
			if((int)$sql1->isMultiLanguage == 1){
				$query .= "LEFT JOIN `". $sql1->tableName. TABLE_ML_SUFFIX. "` ON ";
				$query .= "`". $sql1->tableName. TABLE_ML_SUFFIX. "`.`". $sql1->tableName. "Id` = ";
				$query .= "`". $sql1->tableName. "`.`". $sql1->tableName. "Id` ";
				$query .= " AND ";
				$query .= "`". $sql1->tableName. TABLE_ML_SUFFIX. "`.`langId` = ". $this->langId;
			}
			if((int)$sql1->isTree == 1){
				$query .= " WHERE `". $table. "`.`". $sql1->recursiveField. "` = ". (int)$parentId;
			}
			$sql2->query($query);
			if($sql2->num_rows() > 0){
				$nodeCollection->children = array();
				do{
					$node = & $nodeCollection->children[];
					$node = new stdClass();
					$node->langId = $this->langId;
					$node->parentId = $id;
					$node->id = $id;
					$node->dynamicId = $sql2->$primary;
					$node->relateTable = $sql1->tableName;
					$node->name = $sql2->$value;
					$node->children = $this->getChildNodes($table, $id, $sql2->$primary);
					if((int)$sql1->isTree == 1){
						$this->getDynamicNodes($node, $table, $id, $sql2->$primary);
					}
				}while($sql2->next());
			}
		}else{
			$nodeCollection->children = $this->getChildNodes($table, $id);
		}
		unset($sql2);
		unset($sql1);
	}
}

?>