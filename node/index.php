<?php

$data = isset($_POST['data']) ? $_POST['data'] : "";
$id = isset($_POST['id']) ? $_POST['id'] : "";

$filename = "temp/temp_" . time() . ".gfl";
if( !empty($id) ) {
	if( file_exists("temp/" . $id . ".gfl") ) {
		$filename = "temp/" . $id . ".gfl";
	}
}
$file = fopen($filename, "w"); 

if( !$file) {
	die("File could not be saved.");
}

fwrite($file, $data);
fclose($file);

include_once(dirname(__FILE__) . "/../processor/index.php");
process($filename);

?>