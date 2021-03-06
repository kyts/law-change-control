<?php

class Params 
{
	private $db_conn;
	
	function __construct($db)
	{
		$this->db_conn = $db;
	}
	
	public function getParams() {
		$params = $this->db_conn->query("SELECT * FROM parameters WHERE id=1");
		
		$params_arr = array();
		if($params){
			while($row = $params->fetchArray(SQLITE3_ASSOC)){
     			$params_arr[] = $row;
			}
			return array('params' => $params_arr);
		}
		return FALSE;
	}
	
	public function setParams($params) {
		
		$stmt = $this->db_conn->prepare("UPDATE parameters SET days_i=:days_i, days_z=:days_z, in_redactions=:in_redactions, in_history=:in_history, in_hrefs=:in_hrefs WHERE id=1");
		
		$stmt->bindValue(':days_i', $params['days_i']);
		$stmt->bindValue(':days_z', $params['days_z']);
		
		if($params['in_redactions']){
			$flag1 = 1;
		} else {
			$flag1 = 0;
		}
		$stmt->bindValue(':in_redactions', $flag1);
		
		if($params['in_history']){
			$flag2 = 1;
		} else {
			$flag2 = 0;
		}
		$stmt->bindValue(':in_history', $flag2);
		
		if($params['in_hrefs']){
			$flag3 = 1;
		} else {
			$flag3 = 0;
		}
		$stmt->bindValue(':in_hrefs', $flag3);
		
		$stmt->execute();
		
		return $stmt;
	}	

	public function setParams2($params) {
		
		if($params['days_i']){
			$flag1 = $params['days_i'];
		} else {
			$flag1 = 5;
		}
		
		if($params['days_z']){
			$flag2 = $params['days_z'];
		} else {
			$flag2 = 7;
		}

		if($params['in_redactions']){
			$flag3 = 1;
		} else {
			$flag3 = 0;
		}
				
		if($params['in_history']){
			$flag4 = 1;
		} else {
			$flag4 = 0;
		}
		
		if($params['in_hrefs']){
			$flag5 = 1;
		} else {
			$flag5 = 0;
		}
	
		$stmt = $this->db_conn->query("UPDATE parameters SET days_i=$flag1, days_z=$flag2, in_redactions=$flag3, in_history=$flag4, in_hrefs=$flag5 WHERE id=1");
		return $stmt;
	}

	public function setParamsDefault() {
		$stmt = $this->db_conn->query("UPDATE parameters SET days_i=5, days_z=7, in_redactions=1, in_history=1, in_hrefs=1 WHERE id=1");	
		return $stmt;
	}
}
	



?>