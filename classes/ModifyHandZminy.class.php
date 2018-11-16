<?php

/**
*  
* ##    MARKERS:
* "+" - Deleted (Checked)
* "-" - UnDeleted (UnChecked)
* "p"  - Postponed
* "f"  - Futured
*
* 
*/
class ModifyHandZminy
{
	private $db_conn;
	function __construct($db)
	{
		$this->db_conn = $db;
	}

	public function setChecked($code, $zcode) {
		$stmt = $this->db_conn->exec("UPDATE hand_zminy SET checked='+' WHERE code='".$code."' AND zcode='".$zcode."'");
		return $stmt;
	}

	public function setUnChecked($code, $zcode) {
		$stmt = $this->db_conn->exec("UPDATE hand_zminy SET checked='' WHERE code='".$code."' AND zcode='".$zcode."'");
		return $stmt;
	}

	public function toggleChecked($code, $zcode) {
		$code = trim($code);
		$chkd = $this->db_conn->querySingle("SELECT checked FROM hand_zminy WHERE code='".$code."' AND zcode='".$zcode."'");
		if ($chkd['checked'] === "+") {
			$stmt = $this->setUnChecked($code, $zcode);
		} else {
			$stmt = $this->setChecked($code, $zcode);
		}
		return $stmt;
	}

	public function setPostpone($code, $zcode) {
		$stmt = $this->db_conn->exec("UPDATE hand_zminy SET checked='p' WHERE code='".$code."' AND zcode='".$zcode."'");
		return $stmt;
	}

	public function setFuture($code, $zcode) {

		$stmt = $this->db_conn->exec("UPDATE hand_zminy SET checked='f' WHERE code='".$code."' AND zcode='".$zcode."'");
		return $stmt;
	}

	public function setNoPublik($code, $zcode) {

		$stmt = $this->db_conn->exec("UPDATE hand_zminy SET checked='np' WHERE code='".$code."' AND zcode='".$zcode."'");
		return $stmt;
	}

}



?>