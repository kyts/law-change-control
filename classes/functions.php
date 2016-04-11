<?php

/*
*  Overloaded PHP file listing function:
*  array file_list ( string $directory [, string $file_extension] )
*  $directory  - path without backslash, e.g. "/home/public_html/blarg"
*  $file_extention - optionally filter specific filename extentions, e.g. ".jpg" 
*
*/
//array of files without directories... optionally filtered by extension
function file_list($d,$x){
       foreach(array_diff(scandir($d),array('.','..')) as $f)if(is_file($d.'/'.$f)&&(($x)?ereg($x.'$',$f):1))$l[]=$f;
       return $l;
}
//array of directories
function dir_list($d){
       foreach(array_diff(scandir($d),array('.','..')) as $f)if(is_dir($d.'/'.$f))$l[]=$f;
       return $l;
} 

/////////$files = array_filter(scandir($directory), function($file) { return is_file($file); })




?>
