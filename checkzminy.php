<?php
require_once("classes/MyDB.class.php");
require_once("classes/GetDoc.class.php");
require_once('classes/functions.php');

////////////////////////////////////////////////////////////////////////////
// Open SQLite base
////////////////////////////////////////////////////////////////////////////
$db = new MyDB('zak.sqlite');
if(!$db){
    echo $db->lastErrorMsg();
} else {
   // echo "Opened database successfully\n";
}

$hz = new GetDoc($db);
$zminy = $hz->checkZminy();
$params = $hz->getParameters();
$docs = array();
$current_date = date('Y-m-d', time());

foreach ($zminy as $key => $value) {
	// $zminy[$key]['mark']  ! or z
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
		$docs[] = array_merge($hz->getDocAttrs($value['code']), array('hand_zminy' => array($zminy[$key])));
	}	
}

if (count($docs)>0) {
	echo('{"docs":'.json_encode($docs).'}');
} else {
	echo "FALSE";
} 

$db->close();
?>
