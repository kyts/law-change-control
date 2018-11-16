<?php
////////////////////////////////////////////////////////////////////////////
// Make doc as visualy controlled and checked
////////////////////////////////////////////////////////////////////////////
function __autoload($className) {
  $className = str_replace("..", "", $className);
  require_once("classes/$className.class.php");
}

$code = $_POST["code"];
$zcode = $_POST["zcode"];
 
if(!isset($code) && !isset($zcode)){
	echo "FALSE";
	exit(0);
}

$db = new MyDB('zak.sqlite');
if(!$db){
    echo $db->lastErrorMsg();
} else {
	$hz = new ModifyHandZminy($db);

	switch ($_POST["mode"]) {
		case 'delete':
			$modified = $hz->setChecked($code, $zcode);
			break;
		case 'postpone':
			//$modified = true;
			$modified = $hz->setPostpone($code, $zcode);
			break;
		case 'future':
			//$modified = true;
			$modified = $hz->setFuture($code, $zcode);
			break;
		case 'uncheck':
			//$modified = true;
			$modified = $hz->setUnChecked($code, $zcode);
			break;
		case 'nopublik':
			//$modified = true;
			$modified = $hz->setNoPublik($code, $zcode);
			break;
		
		default:
			$modified = FALSE;
			break;
	}
	if ($modified) {
		echo "OK";
	} else {
		echo "FALSE";
	}

   	
}

$db->close();
?>