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
   	$modified = $hz->toggleChecked($code, $zcode);
   	if ($modified) {
		echo "OK";
	} else {
		echo "FALSE";
	}
}

$db->close();
?>