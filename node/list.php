<?php

$dir = '../node/';

echo "<h1>Files</h1>";
foreach(glob($dir . '*.gfl') as $file) {
	print $file;
}

echo "<h1>Temporary Files</h1>";
echo "<ul>";
foreach(glob($dir . 'temp/*.gfl') as $file) {
	print "<li><a href='../index.php?file=" . basename($file) . "'>" . basename($file) . "</a></li>";
}
echo "</ul>";

?>