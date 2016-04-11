<?php
require_once("classes/MyDB.class.php");
require_once("classes/Params.class.php");
require_once('classes/functions.php');

$db = new MyDB('zak.sqlite');
if(!$db){
    echo $db->lastErrorMsg();
} else {
   // echo "Opened database successfully\n";
}
 
$hz = new Params($db);

if ($hz->setParams($_POST)) {
	echo 'ok';
}

$db->close();
?>