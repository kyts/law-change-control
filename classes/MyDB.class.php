<?php
/**
* Connect to DB
*/
class MyDB extends SQLite3 {
    function __construct($db_file)
    {
      	$this->open($db_file);
    }
}

?>
