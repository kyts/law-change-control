<?php
/**
*  Modify check-flag for Hand Zminy in DB
*/
class ModifyHandZminy
{
	private $db_conn;
	function __construct($db)
	{
		$this->db_conn = $db;
	}

	public function setChecked($code) {
		$stmt = $this->db_conn->exec("UPDATE hand_zminy SET checked='+' WHERE code='".$code."'");
		return $stmt;
	}

	public function setUnChecked($code) {
		$stmt = $this->db_conn->exec("UPDATE hand_zminy SET checked='-' WHERE code='".$code."'");
		return $stmt;
	}

	public function toggleChecked($code) {
		$chkd = $this->db_conn->querySingle("SELECT checked FROM hand_zminy WHERE code='".$code."'");
		if ($chkd['checked'] === "+") {
			$stmt = $this->setUnChecked($code);
		} else {
			$stmt = $this->setChecked($code);			
		}
		return $stmt;
	}
}



?>