<?php
/**
* Getting parameters
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
    echo(json_encode($hz->getParams()));   
}
 
$db->close();
?>
