<?php
function __autoload( $className ) {
  $className = str_replace( "..", "", $className );
  require_once( "classes/$className.class.php" );
}
require_once("classes/functions.php");

$checked_doc = $_POST["code"];
 
$db = new MyDB('zak.sqlite');

if(!$db){
    echo $db->lastErrorMsg();
} else {
    $hz = new ModifyHandZminy($db);
    $modified = $hz->toggleChecked($checked_doc);
    if ($modified) {
	echo "OK";
    } else {
	echo "FALSE";
    }
}

$db->close();
?>
