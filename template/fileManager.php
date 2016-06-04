<?php

class fileManager extends user
{

	const GROUP_UNDEF = "mimeUndefined";

	private $dataCollection;

	public function __construct($name = null, $languageId = 0){
		parent::__construct();
	}

	public function setDataCollectObject(&$data){
		$this->dataCollection = & $data;
	}

	public function render(){
		$sql = new database();

		$this->dataCollection->fm = new stdClass();
		$fm = & $this->dataCollection->fm;

		$fm->group = array();
		$fm->groupCount = array();

		$sql->query("
			SELECT
				COUNT(`fileManager`.`fileManagerId`) as `total`
			FROM
				`fileManager`
			WHERE
				`fileManager`.`sys.mimeTypeId` = 0
		");

		$fm->group[self::GROUP_UNDEF] = $this->kwd(self::GROUP_UNDEF);
		$fm->groupCount[self::GROUP_UNDEF] = $sql->total;

		$sql->query("
			SELECT
				`sys.mimeType`.`group`
				, COUNT(`fileManager`.`fileManagerId`) as `total`
			FROM
				`sys.mimeType`
			LEFT JOIN `fileManager` ON `fileManager`.`sys.mimeTypeId` = `sys.mimeType`.`sys.mimeTypeId`
			GROUP BY
				`sys.mimeType`.`group`
			ORDER BY
				`sys.mimeType`.`group` ASC
		");
		if($sql->num_rows() > 0){
			do{
				$fm->group[$sql->group] = $this->kwd($sql->group);
				$fm->groupCount[$sql->group] = $sql->total;
			}while($sql->next());
		}
	}

	public function xAddProperty($arg, &$json){
		$jsonData = json_decode($arg['data']);
		$data = new stdClass();
		foreach ($jsonData as $key => $value){
			$prop = $value->name;
			$data->$prop = $value->value;
		}
		$sql = new database();
		$sql->exec("
			INSERT INTO
				`fileManagerProperty`
			SET
				`fileManagerProperty`.`fileManagerId` = '". $data->file. "'
				, `fileManagerProperty`.`sys.filePropertyId` = '". $data->property. "'
				, `fileManagerProperty`.`value` = '". $data->value. "'
				, `fileManagerProperty`.`langId` = '". $data->lang. "'
		");
		$id = (int)$sql->insert_id;

		if($id > 0){
			$sql->query("
				SELECT
					`fileManager`.`path`
					, `fileManager`.`hash`
				FROM
					`fileManager`
				WHERE
					`fileManager`.`fileManagerId` = ". $data->file. "
			");
			$mime = mime_content_type($sql->path. "/". $sql->hash);
			$sql->query("
				SELECT
					`sys.mimeType`.`sys.mimeTypeId` as `id`
				FROM
					`sys.mimeType`
				WHERE
					`sys.mimeType`.`phpMime` = '". $mime. "'
			");
			$sql->exec("
				UPDATE
					`fileManager`
				SET
					`fileManager`.`sys.mimeTypeId` = ". $sql->id. "
				WHERE
					`fileManager`.`fileManagerId` = ". $data->file. "
			");
		}

		$sql->query("
			SELECT
				`fileManagerProperty`.`fileManagerPropertyId` as `id`
				, `fileManagerProperty`.`fileManagerId` as `file`
				, `fileManagerProperty`.`sys.filePropertyId` as `property`
				, `fileManagerProperty`.`value` as `value`
				, `fileManagerProperty`.`langId` as `lang`
			FROM
				`fileManagerProperty`
			WHERE
				`fileManagerProperty`.`fileManagerPropertyId` = ". $id. "
		");
		$json->id = $sql->id;
		$json->file = $sql->file;
		$json->property = $sql->property;
		$json->value = $sql->value;
		$json->lang = $sql->lang;
	}

	public function xRemoveProperty($arg, &$json){
		$sql = new database();
		$sql->exec("
			DELETE FROM
				`fileManagerProperty`
			WHERE
				`fileManagerProperty`.`fileManagerPropertyId` = ". (int)$arg['id']. "
		");
		if($sql->affected_rows > 0){
			$json->success = 1;
		}
		unset($sql);
	}

	public function xGetGroup($arg, &$json){
		$sql = new database();
		$sql1 = new database();

		$group = array();

		if($arg['group'] == self::GROUP_UNDEF){
			$group[] = 0;
		}else{
			$sql->query("
				SELECT
					`sys.mimeType`.`sys.mimeTypeId` as `id`
				FROM
					`sys.mimeType`
				WHERE
					`sys.mimeType`.`group` = '". $arg['group']. "'
			");
			do{
				$group[] = $sql->id;
			}while($sql->next());
		}

		//collect languages
		$json->lang = array();
		$sql->query("
			SELECT
				`lang`.`langId`
				, `lang`.`name`
			FROM
				`lang`
			ORDER BY
				`lang`.`default` DESC
		");
		if($sql->num_rows() > 0){
			do{
				$obj = & $json->lang[];
				$obj = new stdClass();
				$obj->id = $sql->langId;
				$obj->value = $sql->name;
			}while($sql->next());
		}

		// collect properties
		$json->property = array();
		$sql->query("
			SELECT
				`sys.fileProperty`.`sys.filePropertyId` as `id`
				, `sys.fileProperty`.`property`
			FROM
				`sys.fileProperty`
			ORDER BY
				`sys.fileProperty`.`sys.filePropertyId` ASC
		");
		if($sql->num_rows() > 0){
			$obj = & $json->property[];
			$obj = new stdClass();
			$obj->id = 0;
			$obj->value = $this->kwd('newProperty');
			do{
				$obj = & $json->property[];
				$obj = new stdClass();
				if(preg_match('/\{([a-zA-Z]{3,})\}/', $sql->property, $matches)){
					$obj->id = $sql->id;
					$sql1->query("
						SELECT
							`sys.dynamic`.`siteMapField` as `value`
							, `sys.dynamic`.`isMultilanguage` as `ml`
						FROM
							`sys.dynamic`
						WHERE
							`sys.dynamic`.`tableName` = '". $matches[1]. "'
					");
					$field = $sql1->value;
					$join = "";
					$tableName = $matches[1];

					if((int)$sql1->ml == 1){
						$join = "JOIN `". $matches[1]. "ML` ON `". $matches[1]. "ML`.`". $matches[1]. "Id` = `". $matches[1]. "`.`". $matches[1]. "Id`
								JOIN `lang` ON `lang`.`langId` = `". $matches[1]. "ML`.`langId`
								WHERE
									`lang`.`default` = 1
						";

						$sql1->query("
							SHOW COLUMNS FROM `". $matches[1]. "ML` LIKE '". $field. "'
						");
						if($sql1->Field != ""){
							$tableName = $matches[1]. "ML";
						}
					}
					$sql1->query("
						SELECT
							`". $tableName. "`.`". $field. "` as `value`
							, `". $matches[1]. "`.`". $matches[1]. "Id` as `id`
						FROM
							`". $matches[1]. "`
						". $join. "
						ORDER BY
							`". $matches[1]. "`.`". $matches[1]. "Id` ASC
					");
					$obj->object = $sql->property;
					$obj->value = $this->kwd($matches[1]);
					if($sql1->num_rows() > 0){
						$obj->row = array();
						do{
							$rowElm = & $obj->row[];
							$rowElm = new stdClass();
							$rowElm->id = $sql1->id;
							$rowElm->value = $sql1->value;
						}while($sql1->next());
					}
				}else{
					$obj->id = $sql->id;
					$obj->value = $this->kwd($sql->property);
				}
			}while($sql->next());
		}

		//collect mime types
		$sql->query("
			SELECT
				`sys.mimeType`.`sys.mimeTypeId` as `id`
				, `sys.mimeType`.`phpMime`
				, `sys.mimeType`.`extension`
				, `sys.mimeType`.`group`
				, `sys.mimeType`.`icon`
			FROM
				`sys.mimeType`
			ORDER BY
				`sys.mimeType`.`group` ASC
		");
		if($sql->num_rows() > 0){
			$json->mime = array();
			do{
				$obj = & $json->mime[];
				$obj = new stdClass();
				$obj->id = $sql->id;
				$obj->mime = $sql->phpMime;
				$obj->ext = $sql->extension;
				$obj->group = $this->kwd($sql->group);
				$obj->icon = $sql->icon;
			}while($sql->next());
		}

		$sql->query("
			SELECT
				`fileManager`.`fileManagerId` as `id`
				, `fileManager`.`hash`
				, `fileManager`.`originalName`
				, `fileManager`.`path`
				, `fileManager`.`size`
			FROM
				`fileManager`
			LEFT JOIN `sys.mimeType` ON `sys.mimeType`.`sys.mimeTypeId` = `fileManager`.`sys.mimeTypeId`
			WHERE
				`fileManager`.`sys.mimeTypeId` IN (". implode(',', $group). ")
		");
		if($sql->num_rows() > 0){
			$json->file = array();
			do{
				$obj = & $json->file[];
				$obj = new stdClass();
				$obj->id = $sql->id;
				$obj->hash = $sql->hash;
				if($sql->size > 0){
					$obj->size = $sql->size;
				}else{
					$obj->size = filesize($sql->path. '/'. $sql->hash);
					$sql1->exec("
						UPDATE
							`fileManager`
						SET
							`fileManager`.`size` = ". $obj->size. "
						WHERE
							`fileManager`.`fileManagerId` = ". $sql->id. "
					");
				}
				$obj->mimeCheck = mime_content_type($sql->path. '/'. $sql->hash);
				$obj->originalName = $sql->originalName;

				//get file properties
				$sql1->query("
					SELECT
						`fileManagerProperty`.`fileManagerPropertyId` as `id`
						, `sys.fileProperty`.`property` as `property`
						, `fileManagerProperty`.`value`
						, `lang`.`name` as `lang`
					FROM
						`fileManagerProperty`
					JOIN `lang` ON `lang`.`langId` = `fileManagerProperty`.`langId`
					JOIN `sys.fileProperty` ON `sys.fileProperty`.`sys.filePropertyId` = `fileManagerProperty`.`sys.filePropertyId`
					WHERE
						`fileManagerProperty`.`fileManagerId` = ". (int)$sql->id. "
				");
				if($sql1->num_rows() > 0){
					$obj->property = array();
					do{
						$prop = & $obj->property[];
						$prop = new stdClass();
						$prop->id = $sql1->id;
						$prop->lang = $sql1->lang;
						if(preg_match('/\{([a-zA-Z]{3,})\}/', $sql1->property, $matches)){
							foreach ($json->property as $propKey => $propValue){
								if(property_exists($propValue, 'object') && $propValue->object == $sql1->property){
									$prop->property = $propValue->value;
									foreach ($propValue->row as $rowKey => $rowValue){
										if($rowValue->id == $sql1->value){
											$prop->value = $rowValue->value;
// 											$prop->lang = "*";
										}
									}
								}

							}
						}else{
							$prop->property = $this->kwd($sql1->property);
							$prop->value = $sql1->value;
						}
					}while($sql1->next());
				}

				//check and sugest mime type
				$sql1->query("
					SELECT
						`sys.mimeType`.`sys.mimeTypeId` as `id`
						, `sys.mimeType`.`icon`
					FROM
						`sys.mimeType`
					WHERE
						`sys.mimeType`.`phpMime` = '". $obj->mimeCheck. "'
				");
				if((int)$sql1->id > 0){
					$obj->sugestMime = $sql1->id;
					$obj->icon = $sql1->icon;
				}else{
					$filePieces = explode(".", $obj->originalName);
					$sql1->exec("
						INSERT INTO `sys.mimeType` SET `sys.mimeType`.`phpMime` = '". $obj->mimeCheck. "', `sys.mimeType`.`extension` = '". $filePieces[count($filePieces) - 1]. "'
					");
					$obj = & $json->mime[];
					$obj = new stdClass();
					$obj->id = $sql1->insert_id;
					$obj->mime = $obj->mimeCheck;
					$obj->ext = $filePieces[count($filePieces) - 1];
					$obj->group = "new";
				}
			}while($sql->next());
		}

		unset($sql);
		unset($sql1);
	}

	public function upload(){
		$sql = new database();
		$json = new stdClass();

		if(!is_dir(UPLOAD_DIR)){
			mkdir(UPLOAD_DIR);
		}

		if($this->isValid() && isset($_FILES['file'])){
			if(is_file($_FILES['file']['tmp_name'])){
				$hashTmp = hash('sha256', file_get_contents($_FILES['file']['tmp_name']));
				move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR. '/'. $hashTmp);
				$hashUpload = hash('sha256', file_get_contents(UPLOAD_DIR. '/'. $hashTmp));
				if($hashTmp == $hashUpload){
					$sql->exec("
						INSERT INTO
							`fileManager`
						SET
							`fileManager`.`hash` = '". $hashUpload. "'
							, `fileManager`.`originalName` = '". $_FILES['file']['name']. "'
							, `fileManager`.`path` = '". UPLOAD_DIR. "'
					");
				}else{
					$json->error("file move error");
				}
			}
		}///usr/local/bin/convert ../www/upload/5d29fa08025585185c3d640ec05ac111dac4fbba54a611dd0cec67e79aebf29e -resize 150x150 ../www/upload/5d29fa08025585185c3d640ec05ac111dac4fbba54a611dd0cec67e79aebf29e.png
		if(!is_dir(IMAGE_DIR)){
			mkdir(IMAGE_DIR);
		}
		if(!is_dir(IMAGE_DIR. '/x150')){
			mkdir(IMAGE_DIR. '/x150');
		}
		$json->msg = array();
		if(strstr(mime_content_type(UPLOAD_DIR. '/'. $hashUpload), 'image')){
			$image = new Imagick(UPLOAD_DIR. '/'. $hashUpload);
			$image->adaptiveResizeImage(150, 150, true);
			file_put_contents (IMAGE_DIR. '/x150/'. $hashUpload, $image);
		}

		//echo json_encode($json);
		unset($sql);
	}

}