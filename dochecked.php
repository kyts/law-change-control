<?php
require_once("classes/MyDB.class.php");
require_once("classes/classModifyHandZminy.php");
require_once('classes/functions.php');

$checked_doc = $_POST["code"];
 
print_r($checked_doc);

$db = new MyDB('zak.sqlite');
if(!$db){
    echo $db->lastErrorMsg();
} else {
   // echo "Opened database successfully\n";
}
 
$hz = new ModifyHandZminy($db);

echo $hz->setChecked($checked_doc);

$db->close();
?>