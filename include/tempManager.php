<?php

class tempManager
{

	public function createTemp($key, $data){
		$fp = fopen(TEMP_DIR. '/'. $key, 'w');
		fwrite($fp, $data);
		fclose($fp);
	}

	public function tempExist($key){
		return file_exists(TEMP_DIR. '/'. $key);
	}

	public function created($key){
		$fs = stat(TEMP_DIR. '/'. $key);
		$date = new DateTime();
		$date->setTimestamp($fs['mtime']);
		return $date->format('d-m-Y H:i:s');
	}
}

?>