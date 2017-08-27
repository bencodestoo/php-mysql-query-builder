<?php
class DB {
	
	protected $db_username, $db_password, $db_name, $db_prefix, $db_server, $conn, $result;
	
	
	public function __construct($config, $database = NULL){
		if(!is_array($config)){
			throw new Exception("Could not initialize Database class. Invalid configuration provided");
		}
		foreach($config as $key => $val){
			$this->$key = $val;
		}
		$this->conn = mysqli_connect($this->db_server, $this->db_username, $this->db_password)or die("Database connection failed.");
		if($database != NULL){
			mysqli_select_db($this->conn, $database);
		}else{
			mysqli_select_db($this->conn, $this->db_name);
		}
		
	}
	
	public function insert($table, $data = array()){
		$table = $this->db_prefix.$table;
		$cols = array();
		$values = array();
		if(!is_array($data)){
			die("Insert data expected as array");
		}
		foreach($data as $k => $v){
			array_push($cols, $k);
			$v = mysqli_real_escape_string($this->conn, $v);
			$v = $this->enclose($v);
			array_push($values, $v);
		}
		$cols = implode(", ", $cols);
		$values = implode(", ", $values);
		$sql = "INSERT INTO ".$table." (".$cols.") VALUES (".$values.")";
		
		$response = $this->run($sql);
		return mysqli_insert_id($this->conn);
	}
	
	public function update($table, $new = array(), $where = array()){
		$table = $this->db_prefix.$table;
		if(!is_array($new) && !is_array($where)){
			die("Update data expected as array");
		}
		$x = array();
		foreach($new as $k => $v){
			$v = mysqli_real_escape_string($this->conn, $v);
			$v = $this->enclose($v);
			array_push($x, $k." = ".$v);
		}
		$new = implode(', ', $x);
		$y = array();
		foreach($where as $k => $v){
			$v = mysqli_real_escape_string($this->conn, $v);
			$v = $this->enclose($v);
			array_push($y, $k." = ".$v);
		}
		$where = implode(' AND ', $y);
		$sql = "UPDATE ".$table." SET ".$new." WHERE ".$where."";
		
		return $this->run($sql);
	}
	
	public function delete($table, $where = array()){
		$table = $this->db_prefix.$table;
		if(!is_array($where)){
			die("Delete condition expected as array");
		}
		$y = array();
		foreach($where as $k => $v){
			$v = mysqli_real_escape_string($this->conn, $v);
			$v = $this->enclose($v);
			array_push($y, $k." = ".$v);
		}
		$where = implode(' AND ', $y);
		$sql  = "DELETE FROM ".$table." WHERE ".$where."";
		
		return $this->run($sql);
	}
	
	public function select($table, $data = NULL, $condition = FALSE){
		$table = $this->db_prefix.$table;
		if(empty($data)){
			$data = '*';
		}else{
			if(!is_array($data)){
				die("Data to select is expected as array");
			}else{
				$data = implode(", ", $data);
			}
		}
		
		if($condition){
			if(is_array($condition)){
				$y = array();
				foreach($condition as $k => $v){
					$v = mysqli_real_escape_string($this->conn, $v);
					$v = $this->enclose($v);
					array_push($y, $k." = ".$v);
				}
				$where = implode(' AND ', $y);
			}else{
				die("Get condition expected as array");
			}
		}
		$sql = "SELECT ".$data." FROM ".$table."";
		if(isset($where)){
			$sql .= " WHERE ".$where;
		}
		
		$data = $this->run($sql);
		$result = array();
		while($dat = mysqli_fetch_array($data)){
			array_push($result, (object) $dat);
		}
		
		return $result;
	}
	
	private function run($sql){
		$result = mysqli_query($this->conn, $sql)or die("Database error: ".mysqli_error($this->conn));
		return $result;
	}
	
	private function enclose($value){
		$str = "'";
		$str .= $value;
		$str .="'";
		return $str;
		
	}

}
?>
