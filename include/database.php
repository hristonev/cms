<?php

class database extends mysqli
{
	public static $instance;
	public static $globals;
	public static $pg_globals;
	private static $opt = array();

	public $result;
	public $row;
	private $saved_thread_id;
	private $is_postgree;
	private $pg_instance;

	public function __construct($host = NULL, $user = NULL, $password = NULL, $database = NULL, $port = NULL, $socket = NULL){

		if(self::$instance instanceof mysqli){
			return self::$instance;
		}else{
			$this->connect();
		}

		if(!isset($_SESSION["__database__"])){
			$_SESSION["__database__"] = array();
		}
		if(!$this->is_postgree){
			$_SESSION["__database__"][$this->thread_id] = $this->thread_id;
			$this->saved_thread_id = $this->thread_id;
			if(count($_SESSION["__database__"]) > 150){
				echo 'max threads!!!';
				$this->flush_threads();
			}
		}
	}

	public static function setOpt($optArray){
		foreach ($optArray as $key => $value){
			self::$opt[$key] = $value;
		}
	}

	private function kill_all(){
		if(isset($_SESSION["__database__"])){
			foreach($_SESSION["__database__"] as $id => $thread_id){
				$this->kill($thread_id);
				unset($_SESSION["__database__"][$id]);
			}
		}
	}

	public function flush_threads(){
		$this->kill_all();
	}

	public function exec($sql){
		if(!$this->is_postgree){
			return parent::query($sql);
		}else{
			return pg_query($this->pg_instance, $sql);
		}
	}

	public function query($sql){
		if(!$this->is_postgree){
			$this->result = parent::query($sql);

			if(!$this->result){
				echo '<pre>';
					trigger_error("Error in mysql query ". $sql. ". \nError: ". $this->error, E_USER_ERROR);
				echo '</pre>';
				$this->kill_all();
				$this->row = null;
			}else{
				$this->row = $this->result->fetch_object();
			}
		}else{
			$this->result = pg_query($this->pg_instance, $sql);
			if(!$this->result){
				echo '<pre>';
					trigger_error("Error in postgreSQL query ". $sql. ". \nError: ". $this->error, E_USER_ERROR);
				echo '</pre>';
			}else{
				$this->row = pg_fetch_object($this->result);
			}
		}

		return $this->row;
	}

	public function __destruct(){
		if(!$this->is_postgree){
			if(isset($_SESSION["__database__"][$this->saved_thread_id])){
				unset($_SESSION["__database__"][$this->saved_thread_id]);
				$this->kill($this->saved_thread_id);
			}
			parent::close();
		}else{
			pg_close($this->pg_instance);
		}
	}

	public function connect($host = NULL, $user = NULL, $password = NULL, $database = NULL, $port = NULL, $socket = NULL){
		if(!$this->is_postgree){
			self::$instance = parent::__construct(self::$opt['host'], self::$opt['user'], self::$opt['password'], self::$opt['database'], self::$opt['port'], self::$opt['socket']);
		}else{
			if(!isset(self::$pg_globals)){
				global $pg_host, $pg_username, $pg_password, $pg_dbname, $pg_port, $pg_socket;
				self::$pg_globals = array($pg_host, $pg_username, $pg_password, $pg_dbname, $pg_port, $pg_socket);

			}

			$conn_string = "host=". self::$pg_globals[0]. " port=". self::$pg_globals[4]. " dbname=". self::$pg_globals[3]. " user=". self::$pg_globals[1]. " password=". self::$pg_globals[2]. "";
			$this->pg_instance = pg_connect($conn_string);
		}

		#parent::query("SET NAMES utf8");
	}

	public function rewind(){
		$this->result->data_seek(0);
	}

	public function current(){

	}

	public function key(){

	}

	public function num_rows(){
		if(is_object($this->result)){
			return $this->result->num_rows;
		}else{
			return null;
		}
	}

	public function next(){
		if(!$this->is_postgree){
			$this->row = $this->result->fetch_object();
		}else{
			$this->row = pg_fetch_object($this->result);
		}

		return $this->row;
	}

	public function valid(){
		return 0;
	}

	public function __get($key){
		if(isset($this->row->$key)){
			$value = $this->row->$key;
		}elseif(isset($this->$key)){
			$value = $this->$key;
		}else{
			$value = '';
		}

		return $value;
	}
}

?>