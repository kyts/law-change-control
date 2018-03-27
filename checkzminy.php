<?php
function __autoload($className) {
  $className = str_replace("..", "", $className);
  require_once("classes/$className.class.php");
}
////////////////////////////////////////////////////////////////////////////
// Check changes in laws
////////////////////////////////////////////////////////////////////////////
$db = new MyDB('zak.sqlite');
if(!$db){
    echo $db->lastErrorMsg();
} else {
    $hz = new GetDoc($db);
    $maxvers = $hz->getProtocolVersMax();
	$zminy = $hz->checkZminy();
	$params = $hz->getParameters();
	$docs = array();
	$current_date = date('Y-m-d', time());

	foreach ($zminy as $key => $value) {
		$ignore_days = 5;
		if ($zminy[$key]['mark']==="z") {
			$ignore_days = $params['days_z'];
		}
		if ($zminy[$key]['mark']==="!") {
			$ignore_days = $params['days_i'];
		}
		$ignore_date = strtotime($current_date.' -'.$ignore_days.' days'); 
		$zminy_date = strtotime($zminy[$key]['updtdate']);	
		if ($zminy_date < $ignore_date) {
			$docs[] = array_merge($hz->getDocAttrs($value['code']), array('hand_zminy' => $zminy[$key]));
		}	
	}
	if (count($docs)>0) {
		$json_docs = json_encode($docs);
		echo("{\"docs\":$json_docs}");
	} else {
		echo "FALSE";
	}
}

$db->close();
?>
