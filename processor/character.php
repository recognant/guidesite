<?php

class Character {

	var $xml = null;
	var $file = null;
	var $sheet = null;

	function __construct($file) {
		if( !isset($file) || $file === null || !file_exists($file))
			throw new Exception("Character not found!");
			
		$this->file = $file;
		$this->sheet = $this->load($file);
		$this->xml = $this->toXML();
	}
	
	private function load($file) {
		$data = array();
		$handle = fopen($file, "r");
		
		while(!feof($handle)){
			$line = rtrim(fgets($handle));
			$data = array_merge($data, $this->parseLine($line));
		}
		
		return $data;
	}
	
	private function parseLine($line) {
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
		if( $this->sheet === null)
			return;
			
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;

		$root = $doc->createElement("character");
		$doc->appendChild($root);
		$gear = $doc->createElement("gear");
		$root->appendChild($gear);
		
		$fileName = $this->file;
		foreach($this->sheet as $key => $value) {
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
		$file = substr($fileName, 0, $length) . ".xml";
		$doc->save($file);
		return $doc;
	}
	
	function toString() {
		if( $this->xml === null ) {
			return;
		}
		
		
	}

}


?>