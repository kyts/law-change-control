<?php
date_default_timezone_set('Europe/Kiev'); 
/**
* 
*/
class GetDoc 
{
	private $db_conn;

	function __construct($db)
	{
		$this->db_conn = $db;
	}
	////////////////////////////////////////////////////////////////////////////
	// Get all doc attributes from DB
	////////////////////////////////////////////////////////////////////////////
	public function getDocAttrs($code) {

		$docs_attributes = $this->db_conn->query("SELECT * FROM docs_attributes WHERE code='".$code."'");

		if ($docs_attributes) {

			$redactions = $this->db_conn->query("SELECT * FROM redactions WHERE code='".$code."' ORDER BY zdate");

			$history = $this->db_conn->query("SELECT * FROM history WHERE code='".$code."' ORDER BY his_date");

			$hrefs = $this->db_conn->query("SELECT * FROM hrefs WHERE code='".$code."'");



//	if (!$docs_attributes) {
//		$errors = $db->lastErrorMsg();
//		echo $errors;
//		return FALSE;
//	}
//	if (!$redactions) {
//		$errors = $db->lastErrorMsg();
//		echo $errors;
//		return FALSE;
//	}
//	if (!$history) {
//		$errors = $db->lastErrorMsg();
//		echo $errors;
//		return FALSE;
//	}
//	if (!$hrefs) {
//		$errors = $db->lastErrorMsg();
//		echo $errors;
//		return FALSE;
//	}


			$docs_attributes_arr = array();
			while($row = $docs_attributes->fetchArray(SQLITE3_ASSOC) ){
				$row['publish'] = $this->getPublishTitle($row['publish']);
				$row['vidy'] = $this->getVidyTitle($row['vidy']);
				if ($row['regdate']==="1970-01-01" or $row['regdate']==="??.??.????") {
					$row['regdate'] = "";
				}

				$docs_attributes_arr[] = $row;
			}

			$redactions_arr = array();
			while($row = $redactions->fetchArray(SQLITE3_ASSOC) ){
				$row['zcode'] = $this->getDocTitle($row['zcode']);
				
				$redactions_arr[] = $row;
			}

			$history_arr = array();
			while($row = $history->fetchArray(SQLITE3_ASSOC) ){
				$row['his_code'] = $this->getDocTitle($row['his_code']);
				if ($row['his_date']==="1970-01-01" or $row['his_date']==="??.??.????") {
					$row['his_date'] = "";
				}
  				$history_arr[] = $row;
			}

			$hrefs_arr = array();
			while($row = $hrefs->fetchArray(SQLITE3_ASSOC) ){
				$row['hrefs_code'] = $this->getDocTitle($row['hrefs_code']);

     			$hrefs_arr[] = $row;
			}
		}

		/*$doc = '{"document":'.json_encode($docs_attributes_arr).', 
			 "redactions":'.json_encode($redactions_arr).',
			 "history":'.json_encode($history_arr).',
			 "hrefs":'.json_encode($hrefs_arr).'
			}';
		*/
		$doc = array('document' => $docs_attributes_arr,
						'redactions' => $redactions_arr,
						'history' => $history_arr,
						'hrefs' => $hrefs_arr);


		return $doc;
	}

	public function getDocRecvizity($code) {
		$docs_attributes = $this->db_conn->query("SELECT * FROM docs_attributes WHERE code='".$code."'");
		if ($docs_attributes) {
			$docs_attributes_arr = array();
			while($row = $docs_attributes->fetchArray(SQLITE3_ASSOC)){
				$row['publish'] = $this->getPublishTitle($row['publish']);
				$row['vidy'] = $this->getVidyTitle($row['vidy']);
				if ($row['regdate']==="1970-01-01" or $row['regdate']==="??.??.????") {
					$row['regdate'] = "";
				}

				$docs_attributes_arr[] = $row;
			}
			//print_r($docs_attributes_arr);
			if (isset($docs_attributes_arr[0])) {
				return $docs_attributes_arr[0];
			}
			return FALSE;
		}
		return FALSE;
	}

	// Вытягиваем параметры из таблицы
	public function getParameters() {
		$params = $this->db_conn->query("SELECT * FROM parameters WHERE id=1");
		if($params){
			$prms = $params->fetchArray(SQLITE3_ASSOC);
			return $prms;
		}
		return FALSE;		
	}

/*	// Проверка наличия изменений из таблицы hand_zminy в таблице redactions.
	public function checkZminyRedactions() {
		$info = $this->db_conn->query("
			SELECT code, zcode, mark, checked, hz_code, hz_zcode, updtdate
			FROM hand_zminy 
			
			LEFT OUTER JOIN 
						(SELECT hz.code AS hz_code, hz.zcode AS hz_zcode, r.code AS r_code, r.zcode AS r_zcode
						 FROM hand_zminy AS hz 
						 INNER JOIN redactions AS r 
						 ON r_code = hz_code AND r_zcode = hz_zcode

						 ) 
						 AS tt
			ON hand_zminy.code = tt.r_code 

			WHERE hz_code IS NULL 
			    AND checked IN ('','-')
			    AND updtdate <= (SELECT date('now','-5 day'))
			");	
		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){
     			$row['zcode'] = $this->getDocTitle($row['zcode']);

     			$info_arr[] = $row;
			}
			return $info_arr;
			//return '{"hand_zminy":'.json_encode($info_arr).'}';
		}
		return FALSE;
	}

	// Проверка наличия изменений из таблицы hand_zminy в таблице history.
	public function checkZminyHistory() {
		$info = $this->db_conn->query("
			SELECT code, zcode, mark, checked, hz_code, hz_zcode, updtdate
			FROM hand_zminy 
			LEFT OUTER JOIN 
						(SELECT hi.code AS hi_code, hi.his_code AS hi_zcode, hz.code AS hz_code, hz.zcode AS hz_zcode 
						 FROM history AS hi 
						 INNER JOIN hand_zminy AS hz 
						 ON hi_code = hz_code AND hi_zcode = hz_zcode) 
						 AS tt
			ON hand_zminy.code = tt.hi_code 
			WHERE hz_code IS NULL AND checked IN ('','-')
			");	
		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){
     			$row['zcode'] = $this->getDocTitle($row['zcode']);

     			$info_arr[] = $row;
			}
			return $info_arr;
			//return '{"hand_zminy":'.json_encode($info_arr).'}';
		}
		return FALSE;
	}

	// Проверка наличия изменений из таблицы hand_zminy в таблице hrefs.
	public function checkZminyHrefs() {
		$info = $this->db_conn->query("
			SELECT code, zcode, mark, checked, hz_code, hz_zcode, updtdate
			FROM hand_zminy 
			LEFT OUTER JOIN 
						(SELECT hr.code AS hr_code, hr.hrefs_code AS hr_zcode, hz.code AS hz_code, hz.zcode AS hz_zcode 
						 FROM hrefs AS hr 
						 INNER JOIN hand_zminy AS hz 
						 ON hr_code = hz_code AND hr_zcode = hz_zcode) 
						 AS tt
			ON hand_zminy.code = tt.hr_code 
			WHERE hz_code IS NULL AND checked IN ('','-')
			");	
		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){
     			$row['zcode'] = $this->getDocTitle($row['zcode']);

     			$info_arr[] = $row;
			}
			return $info_arr;
			//return '{"hand_zminy":'.json_encode($info_arr).'}';
		}
		return FALSE;
	}*/

	public function checkZminy() {
		$params = $this->getParameters();

		if ($params['in_redactions']) {
			$in_redactions_query = "
			LEFT OUTER JOIN
          	 	(
               	SELECT hz.code AS hz_code,
                      hz.zcode AS hz_zcode,
                      r.code AS r_code,
                      r.zcode AS r_zcode
                FROM hand_zminy AS hz
                      INNER JOIN
                      redactions AS r ON r_code = hz_code AND 
                                         r_zcode = hz_zcode
           		)
                AS tt 
            ON hand_zminy.code = tt.r_code
            ";
            $in_red_q = "AND hz_code IS NULL";
		} else {
			$in_redactions_query = $in_red_q = "";
		}

		if ($params['in_history']) {
			$in_history_query = "
			LEFT OUTER JOIN
           		(
                SELECT hi.code AS hi_code,
                      hi.his_code AS hi_zcode,
                      hz1.code AS hz1_code,
                      hz1.zcode AS hz1_zcode
                FROM history AS hi
                      INNER JOIN
                      hand_zminy AS hz1 ON hi_code = hz1_code AND 
                                           hi_zcode = hz1_zcode
           		)
           		AS tt 
           	ON hand_zminy.code = tt.hi_code
            ";
            $in_hi_q = "AND hz1_code IS NULL";
		} else {
			$in_history_query = $in_hi_q = "";
		}

		if ($params['in_hrefs']) {
			$in_hrefs_query = "
			LEFT OUTER JOIN
           		(
                SELECT hr.code AS hr_code,
                      hr.his_code AS hr_zcode,
                      hz2.code AS hz2_code,
                      hz2.zcode AS hz2_zcode
                FROM history AS hr
                      INNER JOIN
                      hand_zminy AS hz2 ON hr_code = hz2_code AND 
                                           hr_zcode = hz2_zcode
           		)
           		AS tt 
           	ON hand_zminy.code = tt.hr_code
            ";
            $in_hi_q = "AND hz2_code IS NULL";
		} else {
			$in_hrefs_query = $in_hr_q = "";
		}
		

		$info = $this->db_conn->query("
			SELECT 
				code,
           		zcode,
          		mark,
         		checked,
           		updtdate
      		FROM hand_zminy
      	
           	$in_redactions_query

           	$in_history_query

           	$in_hrefs_query
            
    		WHERE checked IN ('', '-') 
    		
    			$in_red_q
    			$in_hi_q
    			$in_hi_q

			");	
			/// AND updtdate <= (SELECT date('now','-5 day'))
		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){
     			$row['zcode'] = $this->getDocRecvizity($row['zcode']);

     			$info_arr[] = $row;
			}
			return $info_arr;
			//return '{"hand_zminy":'.json_encode($info_arr).'}';
		}
		return FALSE;
	}




	public function getDocTitle($code) {
		$info = $this->db_conn->query("SELECT title FROM docs_attributes WHERE code='".$code."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;
	}

	public function getHandZminy($code) {
		$info = $this->db_conn->querySingle("SELECT code, zcode FROM hand_zminy WHERE code='".$code."'", true);	
		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC)){
     			$info_arr[] = $row;
			}
			return json_encode($info_arr);
		}
		return FALSE;
	}

	public function getProtocolsVers() {
		$info = $this->db_conn->query("SELECT MAX(vers) AS maxvers, MIN(vers) AS minvers FROM docs_attributes");
		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){
     			$info_arr[] = $row;
			}
			return $info_arr;
			//return '{"vers":'.json_encode($info_arr).'}';
		}
		return FALSE;
	}

	public function getPublishTitle($publish) {
		$info = $this->db_conn->query("SELECT title FROM publish WHERE publish = '".$publish."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;
	}

	public function getVidyTitle($vidy) {
		$info = $this->db_conn->query("SELECT title FROM vidy WHERE vidy = '".$vidy."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;	
	}

}


?>