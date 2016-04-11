<?php
/**
* Setting parameters
*/
function __autoload( $className ) {
  $className = str_replace( "..", "", $className );
  require_once( "classes/$className.class.php" );
}

$db = new MyDB('zak.sqlite');
if(!$db){
    echo $db->lastErrorMsg();
} else {
   $hz = new Params($db);
	if ($hz->setParams($_POST)) {
		echo 'ok';
	}
}
 
$db->close();
?>
