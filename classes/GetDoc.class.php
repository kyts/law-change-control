<?php
/**
* Working with Doc information
*/
class GetDoc 
{
	private $db_conn;

	function __construct($db)
	{
		$this->db_conn = $db;
	}

	private function formatDate($date) {
		return date_format(date_create($date), 'd.m.Y');
		#output: 2012-03-24 17:45:12
	}
	
	// Getting all requisites of the document according to number
	public function getDocAttrs($code) {
		$code = trim($code);
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
				} else {
					$row['regdate'] = $this->formatDate($row['regdate']);
				}
				$row['updtdate'] = $this->formatDate($row['updtdate']);
				$row['doc_date'] = $this->formatDate($row['doc_date']);

				$docs_attributes_arr[] = $row;
			}

			$redactions_arr = array();
			while($row = $redactions->fetchArray(SQLITE3_ASSOC) ){
				$row['zcode'] = $this->getDocTitle($row['zcode']);
				$row['zdate'] = $this->formatDate($row['zdate']);
				$row['zfrom'] = $this->formatDate($row['zfrom']);			
				$redactions_arr[] = $row;
			}

			$history_arr = array();
			while($row = $history->fetchArray(SQLITE3_ASSOC) ){
				$row['his_code'] = $this->getDocTitle($row['his_code']);
				if ($row['his_date']==="1970-01-01" or $row['his_date']==="??.??.????") {
					$row['his_date'] = "";
				}
				$row['his_date'] = $this->formatDate($row['his_date']);
  				$history_arr[] = $row;
			}

			$hrefs_arr = array();
			while($row = $hrefs->fetchArray(SQLITE3_ASSOC) ){
				$row['hrefs_code'] = $this->getDocTitle($row['hrefs_code']);
     			$hrefs_arr[] = $row;
			}
		}

		$doc = array('document' => $docs_attributes_arr,
				'redactions' => $redactions_arr,
				'history' => $history_arr,
				'hrefs' => $hrefs_arr);
		return $doc;
	}

	// Getting simple requisites of the document according to number
	public function getDocRecvizity($code) {
		$code = trim($code);
		$docs_attributes = $this->db_conn->query("SELECT * FROM docs_attributes WHERE code='".$code."'");
		if ($docs_attributes) {
			$docs_attributes_arr = array();
			while($row = $docs_attributes->fetchArray(SQLITE3_ASSOC)){
				$row['publish'] = $this->getPublishTitle($row['publish']);
				$row['vidy'] = $this->getVidyTitle($row['vidy']);
				if ($row['regdate']==="1970-01-01" or $row['regdate']==="??.??.????") {
					$row['regdate'] = "";
				}else {
					$row['regdate'] = $this->formatDate($row['regdate']);
				}
				$row['updtdate'] = $this->formatDate($row['updtdate']);
				$row['doc_date'] = $this->formatDate($row['doc_date']);
				$docs_attributes_arr[] = $row;
			}
			if (isset($docs_attributes_arr[0])) {
				return $docs_attributes_arr[0];
			}
		}
		return FALSE;
	}

	// Get parameters from table
	public function getParameters() {
		$params = $this->db_conn->query("SELECT * FROM parameters WHERE id=1");
		if($params){
			$prms = $params->fetchArray(SQLITE3_ASSOC);
			return $prms;
		}
		return FALSE;		
	}

	// Checking Zminy (changes)
	public function checkZminy() {
		// Вытягиваем параметры
		$params = $this->getParameters();

		// Checking changes from table 'hand_zminy' in table 'redactions'.
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
            $in_red_q = "hz_code IS NULL";
        }

        // Checking changes from table 'hand_zminy' in table 'history'
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
            $in_hi_q = "hz1_code IS NULL";
        }

        // Checking changes from table 'hand_zminy' in table 'hrefs'
		if ($params['in_hrefs']) {
			$in_hrefs_query = "
		LEFT OUTER JOIN
           		(
                SELECT hr.code AS hr_code,
                  hr.hrefs_code AS hr_zcode,
                  hz2.code AS hz2_code,
                  hz2.zcode AS hz2_zcode
                FROM hrefs AS hr
                      INNER JOIN
                      hand_zminy AS hz2 ON hr_code = hz2_code AND 
                                           hr_zcode = hz2_zcode
           		)
           		AS tt 
           	ON hand_zminy.code = tt.hr_code
            ";
            $in_hr_q = "hz2_code IS NULL";
        }
		
        // Формирование строк запроса в зависимости от параметров
        // Formating query string depending from parameters
		if ($params['in_redactions'] or $params['in_history'] or $params['in_hrefs']) {
			$in_cheked = "checked IN ('', '-')";
		}
		$str_join ="";
		if (isset($in_redactions_query)) { $str_join = "{$str_join} {$in_redactions_query}"; }
		if (isset($in_history_query)) { $str_join = "{$str_join} {$in_history_query}"; }
		if (isset($in_hrefs_query)) { $str_join = "{$str_join} {$in_hrefs_query}"; }
		$str = "";
		if (isset($in_cheked)) { 
			$str = "WHERE {$in_cheked}";
			if (isset($in_red_q)) {	$str = " {$str} AND {$in_red_q} "; }
			if (isset($in_hi_q)) { $str = " {$str} AND {$in_hi_q} "; }
			if (isset($in_hr_q)) { $str = " {$str} AND {$in_hr_q} "; }
		} else {
			if (isset($in_red_q)) {
				$str = "WHERE {$in_red_q} ";
				if (isset($in_hi_q)) { $str = " {$str} AND {$in_hi_q} "; }
				if (isset($in_hr_q)) { $str = " {$str} AND {$in_hr_q} "; }
			} else {
				if (isset($in_hi_q)) {
					$str = "WHERE {$in_hi_q} ";
					if (isset($in_hr_q)) { $str = " {$str} AND {$in_hr_q} "; }
				} else {
					if (isset($in_hr_q)) { $str = "WHERE {$in_hr_q} "; }
				}	
			}
		}

		// Основной запрос к базе
		// Main query 
		$info = $this->db_conn->query("
		SELECT 
			code,
           		zcode,
          		mark,
         		checked,
           		updtdate
      		FROM hand_zminy
           	{$str_join}
            	{$str}
			");	

		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){
     			$row['zcode'] = $this->getDocRecvizity($row['zcode']);
     			$row['updtdate'] = $this->formatDate($row['updtdate']);
     			$info_arr[] = $row;
			}
			return $info_arr;
		}
		return FALSE;
	}

	// Get Hand Zminy (changes) from DB 
	public function getHandZminy($code) {
		$code = trim($code);
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

	// Get min and max protocol versions from DB
	public function getProtocolsVers() {
		$info = $this->db_conn->query("SELECT MAX(vers) AS maxvers, MIN(vers) AS minvers FROM docs_attributes");
		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){
     			$info_arr[] = $row;
			}
			return $info_arr;
		}
		return FALSE;
	}

	// Get max protocol version from DB
	public function getProtocolVersMax() {
		$info = $this->db_conn->query("SELECT MAX(vers) AS maxvers FROM docs_attributes");
		if($info){
			$maxvers = $info->fetchArray(SQLITE3_ASSOC);
			return $maxvers['maxvers'];
		}
		return FALSE;
	}

	// Get document title from DB
	public function getDocTitle($code) {
		$code = trim($code);
		$info = $this->db_conn->query("SELECT title FROM docs_attributes WHERE code='".$code."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;
	}

	// Get publication title title from DB
	public function getPublishTitle($publish) {
		$publish = trim($publish);
		$info = $this->db_conn->query("SELECT title FROM publish WHERE publish = '".$publish."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;
	}

	// Get document document vidy title from DB
	public function getVidyTitle($vidy) {
		$vidy = trim($vidy);
		$info = $this->db_conn->query("SELECT title FROM vidy WHERE vidy = '".$vidy."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;	
	}
}


?>
