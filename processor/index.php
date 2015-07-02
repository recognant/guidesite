<?php

include_once(dirname(__FILE__) . "/tokenizer.php");
include_once(dirname(__FILE__) . "/parser.php");
include_once(dirname(__FILE__) . "/transformer.php");

$dir = '../node/';

function getNodes() {

	global $dir;
	
	foreach(glob($dir . '*.gfl') as $file) {
		print $file;
	}

}

function process($tag) {

	global $dir;
	
	if(!file_exists($dir . $tag)) {
		return false;
	}
	
	$file = $dir . $tag;
	$handle = fopen($file, "r");
	$tokenstream = (new Tokenizer($handle))->tokenize();
	
	$parser = new Parser();
	$parser->parse($tokenstream);
	
	global $tree;
	$xml = $tree->toXML($file);
	
	$info = pathinfo($file);
	$xmlFile = $info["dirname"] . "/" . $info["filename"] . ".xml"; 
	
	$transformer = new Transformer($xmlFile);
	echo $transformer->transform();
}

?>