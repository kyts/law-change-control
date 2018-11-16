<?php
/**
* Обработка изменений
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

	public function getRedactions($code){
		$code = trim($code);
		$redactions = $this->db_conn->query("SELECT * FROM redactions WHERE code='".$code."' ORDER BY zdate");
		$redactions_arr = array();
		while($row = $redactions->fetchArray(SQLITE3_ASSOC) ){
			$row['zcode'] = $this->getDocTitle($row['zcode']);
			$row['zdate'] = $this->formatDate($row['zdate']);
			$row['zfrom'] = $this->formatDate($row['zfrom']);			
			$redactions_arr[] = $row;
		}
		return $redactions_arr;
	}

	public function getHistory($code){
		$code = trim($code);
		$history = $this->db_conn->query("SELECT * FROM history WHERE code='".$code."' ORDER BY his_date");
		$history_arr = array();
		while($row = $history->fetchArray(SQLITE3_ASSOC) ){
			$row['his_code'] = $this->getDocTitle($row['his_code']);
			if ($row['his_date']==="1970-01-01" or $row['his_date']==="??.??.????") {
				$row['his_date'] = "";
			}
			$row['his_date'] = $this->formatDate($row['his_date']);
			$history_arr[] = $row;
		}
		return $history_arr;
	}

	public function getHrefs($code){
		$code = trim($code);
		$hrefs = $this->db_conn->query("SELECT * FROM hrefs WHERE code='".$code."'");
		$hrefs_arr = array();
		while($row = $hrefs->fetchArray(SQLITE3_ASSOC) ){
			$row['hrefs_code'] = $this->getDocTitle($row['hrefs_code']);
			$hrefs_arr[] = $row;
		}
		return $hrefs_arr;
	}

	// Get all doc attributes from DB
	public function getDocAttrs($code) {
		$code = trim($code);
		$docs_attributes = $this->db_conn->query("SELECT * FROM docs_attributes WHERE code='".$code."'");

		if ($docs_attributes) {

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
		}
		if ( count($docs_attributes_arr)>0 )
			$d = $docs_attributes_arr[0];

		$doc = array(   'document'   => $d,
						'redactions' => '',	// $this->getRedactions($code)
						'history'    => '', // $this->getHistory($code)
						'hrefs'      => ''  // $this->getHrefs($code)
					);
		return $doc;
	}

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

	// Вытягиваем параметры из таблицы
	public function getParameters() {
		$params = $this->db_conn->query("SELECT * FROM parameters WHERE id=1");
		if($params){
			$prms = $params->fetchArray(SQLITE3_ASSOC);
			return $prms;
		}
		return FALSE;		
	}

	// Проверка изменений
	public function checkZminy($fil) {
		// Вытягиваем параметры
		$params = $this->getParameters();

		// Проверка наличия изменений из таблицы hand_zminy в таблице redactions.
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
			AS tt1 
			ON hand_zminy.code = tt1.r_code and hand_zminy.zcode = tt1.r_zcode
			";
			$in_red_q = "hz_code IS NULL";
		}

        // Проверка наличия изменений из таблицы hand_zminy в таблице history
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
			AS tt2 
			ON hand_zminy.code = tt2.hi_code and hand_zminy.zcode = tt2.hi_zcode
			";
			$in_hi_q = "hz1_code IS NULL";
		}

        // Проверка наличия изменений из таблицы hand_zminy в таблице hrefs
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
			AS tt3 
			ON hand_zminy.code = tt3.hr_code and hand_zminy.zcode = tt3.hr_zcode
			";
			$in_hr_q = "hz2_code IS NULL";
		}
		
        // Формирование строк запроса в зависимости от параметров
		if ($params['in_redactions'] or $params['in_history'] or $params['in_hrefs']) {
			//$in_cheked = "checked IN ('', '-', 'p', 'f')";
			$in_cheked = "checked='".$fil."'";
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
		$sql_string = "SELECT 
			code,
			zcode,
			mark,
			checked,
			updtdate
			FROM hand_zminy
			{$str_join}
			{$str}
			";
		$info = $this->db_conn->query($sql_string);	

		if($info){
			$info_arr = array();
			while($row = $info->fetchArray(SQLITE3_ASSOC) ){			
				$dr = $this->getDocRecvizity($row['zcode']);
				if( is_array($dr) && count($dr) > 0 )
					$row['zcode'] = $dr;
				$row['updtdate'] = $this->formatDate($row['updtdate']);
				$info_arr[] = $row;
			}
			return $info_arr;
		}
		return FALSE;
	}

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

	public function getProtocolVersMax() {
		$info = $this->db_conn->query("SELECT MAX(vers) AS maxvers FROM docs_attributes");
		if($info){
			$maxvers = $info->fetchArray(SQLITE3_ASSOC);
			return $maxvers['maxvers'];
		}
		return FALSE;
	}


	public function getDocTitle($code) {
		$code = trim($code);
		$info = $this->db_conn->query("SELECT title FROM docs_attributes WHERE code='".$code."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;
	}

	public function getPublishTitle($publish) {
		$publish = trim($publish);
		$info = $this->db_conn->query("SELECT title FROM publish WHERE publish = '".$publish."'");
		if($info){
			$title = $info->fetchArray(SQLITE3_ASSOC);
			return $title['title'];
		}
		return FALSE;
	}

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