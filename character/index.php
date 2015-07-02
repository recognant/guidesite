<?php

include_once(dirname(__FILE__) . "/../processor/character.php");

$dir = "/character";
$xml = null;
$file = null;
$sheet = null;

$data = isset($_POST['data']) ? $_POST['data'] : "";

if( !empty($data) && $data !== null ) {
	$file = fgets("temp_" . time() . ".txt", "w");
}

function import($f = null) {
	if( !isset($f) || $f === null || !file_exists($f))
		throw new Exception("Character not found!");
		
	global $file, $sheet, $xml;
	$file = $f;
	$sheet = load($f);
	$xml = toXML();
}

function load($file) {
	$data = array();
	$handle = fopen($file, "r");
	
	while(!feof($handle)){
		$line = rtrim(fgets($handle));
		$data = array_merge($data, parseLine($line));
	}
	
	return $data;
}

function parseLine($line) {
	$isValid = preg_match("/^(\s*\w+\s*\=\s*((\w|\_|\,|\d|\=)+)\s*)$/i", $line) > 0;
	
	if( !$isValid )
		return array();
		
	$data = array();
	
	preg_match("/^(\s*(?P<key>\w+)\s*\=\s*(?P<value>(\w|\_|\,|\d|\=)+)\s*)$/i", $line, $matches);
	if( sizeof($matches["key"]) > 0) {
		$key = $matches["key"];
		$value = $matches["value"];
		
		$variables = array();
		preg_match_all("/((?P<key>(bonus\_id|id|enchant\_id))\=(?P<value>(\w|\d)+))/i", $value, $vars);
		if( sizeof($vars["key"]) > 0) {
			$i = 0;
			while($i < sizeof($vars["key"])) {
				$variables[$vars["key"][$i]] = $vars["value"][$i];
				$i++;
			}
		}
		
		$data[$key] = array("value" => $value, "vars" => $variables);
	}
	
	return $data;
}

function toXML() {
	global $sheet, $file;
	
	if( $sheet === null)
		return;
		
	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->preserveWhiteSpace = false;
	$doc->formatOutput = true;

	$root = $doc->createElement("character");
	$doc->appendChild($root);
	$gear = $doc->createElement("gear");
	$root->appendChild($gear);
	
	$fileName = $file;
	foreach($sheet as $key => $value) {
		switch($key) {
		case "monk":
		case "priest":
		case "shaman":
		case "druid":
		case "warlock":
		case "warrior":
		case "hunter":
		case "deathknight":
		case "rogue":
		case "mage":
		case "paladin":
			$fileName = $value["value"];
			$root->setAttribute("name", $value["value"]);
			$root->setAttribute("class", $key);
			break;
		case "level":
			$root->setAttribute("level", $value["value"]);
			break;
		case "race":
			$root->setAttribute("race", $value["value"]);
			break;
		case "role":
			$root->setAttribute("role", $value["value"]);
			break;
		case "head":
		case "neck":
		case "shoulder":
		case "chest":
		case "waist":
		case "legs":
		case "feet":
		case "wrist":
		case "finger1":
		case "finger2":
		case "trinket1":
		case "trinket2":
		case "back":
		case "main_hand":
		case "off_hand":
			$e = $doc->createElement("item");
			$e->setAttribute("slot", $key);
			
			$vars = $value["vars"];
			foreach($vars as $vkey => $vvalue) {
				$e->setAttribute($vkey, $vvalue);
			}
			
			$e->appendChild( $doc->createTextNode($value["value"]) );
			$gear->appendChild($e);
			break;
		default:
		}
	}
	
	$doc->normalize();
	
	$length = strrpos($fileName, ".");
	if( !$length )
		$length = strlen($fileName);
	$f = substr($fileName, 0, $length) . ".xml";
	$doc->save($f);
	return $doc;
}

?>