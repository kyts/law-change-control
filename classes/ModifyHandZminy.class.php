<?php

/**
* 
*/
class ModifyHandZminy
{
	private $db_conn;
	function __construct($db)
	{
		$this->db_conn = $db;
	}

	public function setChecked($code) {
		$stmt = $this->db_conn->querySingle("UPDATE hand_zminy SET checked='+' WHERE code='".$code."'");
		return $stmt;
	}

}



?>