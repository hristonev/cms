<?php
class request
{

	public function __construct(){

	}

	public function render(){
		switch($_GET['type']){
			case 'image':
				$this->loadImage($_GET['id']);
				break;
		}
	}

	private function loadImage($id){
		$sql = new database();
		$sql->query("
			SELECT
				`fileManager`.`hash`
			FROM
				`fileManager`
			WHERE
				`fileManager`.`fileManagerId` = ". (int)$_GET['id']. "
		");
		if(isset($_GET['size'])){
			$size = (int)$_GET['size'];
			if(is_file(IMAGE_DIR. '/x'. $size. '/'. $sql->hash)){
				header('Content-Type: '. mime_content_type(IMAGE_DIR. '/x'. $size. '/'. $sql->hash));
				echo file_get_contents(IMAGE_DIR. '/x'. $size. '/'. $sql->hash);
			}else{
				if(!is_dir(IMAGE_DIR. '/x'. $size)){
					mkdir(IMAGE_DIR. '/x'. $size);
				}
				$image = new Imagick(UPLOAD_DIR. '/'. $sql->hash);
				header('Content-Type: '. $image->getImageMimeType());
				$image->adaptiveResizeImage($size, $size, true);
				file_put_contents (IMAGE_DIR. '/x'. $size. '/'. $sql->hash, $image);
				echo $image;
			}
		}
		unset($sql);
	}

}